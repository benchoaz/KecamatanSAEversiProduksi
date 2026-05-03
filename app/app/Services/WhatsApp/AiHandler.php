<?php

namespace App\Services\WhatsApp;

use App\Models\AppProfile;
use App\Models\ServiceNode;
use App\Models\ServiceRequirement;
use App\Models\PelayananFaq;
use App\Models\AiMemory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AiHandler
{
    /**
     * Handle the incoming message using AI if active.
     * Returns null if AI is inactive or disabled, forcing the system to use traditional fallback.
     */
    public function handle(string $phone, string $message): ?array
    {
        $profile = AppProfile::first();
        
        // Jika belum dikonfigurasi atau AI dimatikan, kembalikan null agar ditangani oleh fallback tradisional
        if (!$profile || !$profile->is_ai_active) {
            return null;
        }

        // 1. Memori AI (Ingat Nama)
        $userName = 'Belum diketahui';
        $memory = null;
        if ($phone) {
            $phoneClean = preg_replace('/[^0-9]/', '', $phone);
            $memory = AiMemory::firstOrCreate(['phone_number' => $phoneClean]);
            $userName = $memory->user_name ?? 'Belum diketahui';
        }

        $regionName = $profile->region_name ?? 'Kecamatan SAE';
        
        // Ambil Pengetahuan dari Database (Service & FAQ)
        $knowledgeBase = $this->getDynamicKnowledge();
        
        // Prompt Baru Berdasarkan Instruksi User
        $systemPrompt = "Anda adalah AI Chatbot resmi layanan publik yang mewakili {$regionName}.\n\n";
        $systemPrompt .= "TUJUAN UTAMA:\n";
        $systemPrompt .= "Memberikan pelayanan prima, cepat, tepat, sopan, dan profesional kepada masyarakat, dengan tetap mengikuti alur workflow sistem yang telah ditentukan.\n\n";
        
        $systemPrompt .= "GAYA KOMUNIKASI:\n";
        $systemPrompt .= "- Gunakan bahasa Indonesia formal, sopan, dan mudah dipahami\n";
        $systemPrompt .= "- Nada ramah, tidak kaku, tidak bertele-tele\n";
        $systemPrompt .= "- Hindari bahasa teknis yang sulit dimengerti masyarakat umum\n";
        $systemPrompt .= "- Berikan kesan 'melayani dengan empati'\n\n";
        
        $systemPrompt .= "ATURAN UTAMA:\n";
        $systemPrompt .= "1. SELALU ikuti struktur workflow yang tersedia dalam sistem\n";
        $systemPrompt .= "2. Jangan memberikan jawaban di luar menu/alur yang sudah ditentukan\n";
        $systemPrompt .= "3. Jika user keluar konteks, arahkan kembali ke pilihan layanan\n";
        $systemPrompt .= "4. Jika data belum lengkap, minta dengan jelas dan sopan\n";
        $systemPrompt .= "5. Jika terjadi error/sistem tidak tersedia, beri alternatif solusi\n\n";
        
        $systemPrompt .= "PENANGANAN KONDISI KHUSUS:\n";
        $systemPrompt .= "[1] KEDARURATAN (Kecelakaan, Bencana, Kebakaran): Respon cepat, singkat, arahkan ke 112 atau 119.\n";
        $systemPrompt .= "[2] KRIMINAL: Arahkan ke kepolisian (110).\n";
        $systemPrompt .= "[3] KESEHATAN: Saran umum, arahkan ke faskes terdekat.\n\n";
        
        $systemPrompt .= "DATA LAYANAN & INFORMASI RESMI (Gunakan data ini sebagai referensi utama):\n";
        $systemPrompt .= "{$knowledgeBase}\n\n";

        $systemPrompt .= "KONTEKS PENGGUNA SAAT INI:\n";
        $systemPrompt .= "- Nama Pengguna: {$userName}\n";
        
        if ($userName === 'Belum diketahui') {
            $systemPrompt .= "- INSTRUKSI MEMORI: Jika pengguna memberitahu namanya, Anda WAJIB menyapa mereka dengan nama tersebut di respon ini. SANGAT PENTING: Untuk membantu sistem mengingat, jika user memberitahu namanya, Anda HARUS menyertakan tag [SET_NAME:nama_pengguna] di paling akhir jawaban Anda (contoh: [SET_NAME:Budi]).\n";
        } else {
            $systemPrompt .= "- INSTRUKSI MEMORI: Gunakan nama {$userName} untuk menyapa pengguna agar lebih akrab namun tetap sopan.\n";
        }
        
        $systemPrompt .= "\nBATASAN: Jangan beropini, jangan bercanda, jangan keluar dari konteks pelayanan publik.\n";
        $systemPrompt .= "PRIORITAS: Kecepatan respon > Kejelasan informasi > Kepatuhan workflow > Kesopanan";

        $provider = $profile->ai_provider ?? 'gemini';
        $reply = "Maaf, terjadi kesalahan saat menghubungi server AI.";

        try {
            if ($provider === 'gemini') {
                $reply = $this->askGemini($profile->google_api_key, $systemPrompt, $message);
            } elseif ($provider === 'openai') {
                $reply = $this->askOpenAI($profile->openai_api_key, $systemPrompt, $message);
            } elseif (in_array($provider, ['deepseek', 'xai', 'openrouter', 'dashscope'])) {
                $reply = $this->askOpenAICompatible($provider, $profile, $systemPrompt, $message);
            } else {
                return null;
            }
            
            // Post-Processing: Deteksi & Simpan Nama (Memory)
            if (preg_match('/\[SET_NAME:(.*?)\]/', $reply, $matches)) {
                $detectedName = trim($matches[1]);
                if ($memory && !empty($detectedName)) {
                    $memory->user_name = $detectedName;
                    $memory->save();
                    Log::info("AI Memory Updated (Service): Name '{$detectedName}' stored for phone {$phoneClean}");
                }
                $reply = str_replace($matches[0], '', $reply);
            }

            // Berhasil mendapat jawaban AI
            return [
                'success' => true,
                'intent' => 'ai_assistant',
                'reply' => trim($reply),
                'state_update' => null,
            ];
            
        } catch (\Exception $e) {
            Log::error("WhatsApp AI Error ({$provider}): " . $e->getMessage());
            return null; 
        }
    }

    private function askGemini($apiKey, $systemPrompt, $message)
    {
        if (empty($apiKey)) throw new \Exception("Google API Key belum diisi.");

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
        
        $response = Http::timeout(15)->post($url, [
            'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
            'contents' => ['parts' => [['text' => $message]]]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Maaf, respon gagal dipahami.";
        }
        throw new \Exception("Gemini Error: " . $response->body());
    }

    private function askOpenAI($apiKey, $systemPrompt, $message)
    {
        if (empty($apiKey)) throw new \Exception("OpenAI API Key belum diisi.");

        $response = Http::withHeaders(['Authorization' => "Bearer {$apiKey}"])
            ->timeout(15)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message],
                ]
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? "Maaf, respon gagal dipahami.";
        }
        throw new \Exception("OpenAI Error: " . $response->body());
    }

    private function askOpenAICompatible($provider, $profile, $systemPrompt, $message)
    {
        $apiKey = '';
        $baseUrl = '';
        $model = '';

        if ($provider === 'deepseek') {
            $apiKey = $profile->deepseek_api_key;
            $baseUrl = 'https://api.deepseek.com/chat/completions';
            $model = 'deepseek-chat';
        } elseif ($provider === 'xai') {
            $apiKey = $profile->xai_api_key;
            $baseUrl = 'https://api.x.ai/v1/chat/completions';
            $model = 'grok-beta';
        } elseif ($provider === 'openrouter') {
            $apiKey = $profile->openrouter_api_key;
            $baseUrl = 'https://openrouter.ai/api/v1/chat/completions';
            $model = 'google/gemini-flash-1.5';
        } elseif ($provider === 'dashscope') {
            $apiKey = $profile->dashscope_api_key;
            $baseUrl = 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions';
            $model = 'qwen-turbo';
        }

        if (empty($apiKey)) throw new \Exception("API Key untuk {$provider} kosong.");

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'HTTP-Referer' => config('app.url'),
        ])->timeout(15)->post($baseUrl, [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message],
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? "Maaf, respon gagal dipahami.";
        }
        throw new \Exception("{$provider} Error: " . $response->body());
    }

    /**
     * Build the knowledge base from database (Cached for 10 minutes)
     */
    private function getDynamicKnowledge(): string
    {
        return Cache::remember('whatsapp_ai_knowledge', 600, function() {
            $knowledge = "DAFTAR LAYANAN TERSEDIA:\n";
            
            // 1. Fetch Service Nodes (Main services)
            $nodes = ServiceNode::where('is_active', true)->get();
            foreach ($nodes as $node) {
                $knowledge .= "- " . strtoupper($node->name) . ": " . ($node->description ?? 'Layanan administrasi') . "\n";
                
                // Get requirements for this node
                $requirements = ServiceRequirement::where('service_node_id', $node->id)
                    ->where('is_active', true)
                    ->get();
                    
                if ($requirements->count() > 0) {
                    $knowledge .= "  Persyaratan: " . $requirements->pluck('name')->implode(', ') . ".\n";
                }
            }

            // 2. Fetch FAQ
            $faqs = PelayananFaq::all();
            if ($faqs->count() > 0) {
                $knowledge .= "\nPERTANYAAN UMUM (FAQ):\n";
                foreach ($faqs as $faq) {
                    $knowledge .= "Tanya: {$faq->question}\nJawab: {$faq->answer}\n";
                }
            }

            return $knowledge;
        });
    }
}
