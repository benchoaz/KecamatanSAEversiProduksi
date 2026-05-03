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
     * 
     * @param string $phone
     * @param string $message
     * @return array|null
     */
    public function handle(string $phone, string $message): ?array
    {
        $profile = AppProfile::first();
        
        if (!$profile || !$profile->is_ai_active) {
            return null;
        }

        // 1. Memori AI (Ingat Nama & Konteks Percakapan)
        $userName = 'Belum diketahui';
        $history = [];
        $memory = null;
        
        if ($phone) {
            $phoneClean = preg_replace('/[^0-9]/', '', $phone);
            $memory = AiMemory::firstOrCreate(['phone_number' => $phoneClean]);
            $userName = $memory->user_name ?? 'Belum diketahui';
            
            // Load rolling context (History)
            if (!empty($memory->context)) {
                $history = json_decode($memory->context, true) ?: [];
            }
        }

        $regionName = $profile->region_name ?? 'Kecamatan Besuk';
        $knowledgeBase = $this->getDynamicKnowledge();
        
        // PROMPT MANUSIAWI & EMPATI
        $systemPrompt = "Anda adalah 'SAE-Bot', asisten virtual resmi yang ramah dan cerdas dari {$regionName}.\n\n";
        $systemPrompt .= "KEPRIBADIAN ANDA:\n";
        $systemPrompt .= "- Anda adalah sosok yang melayani dengan tulus, sabar, dan penuh empati.\n";
        $systemPrompt .= "- Gunakan sapaan yang hangat seperti 'Bapak/Ibu', 'Kakak', atau langsung menyapa nama jika sudah tahu.\n";
        $systemPrompt .= "- Hindari jawaban yang terlalu kaku seperti robot. Gunakan variasi kalimat yang natural.\n";
        $systemPrompt .= "- Jika user bertanya hal yang sama, jangan mengulang jawaban yang sama persis, gunakan pendekatan berbeda.\n\n";
        
        $systemPrompt .= "TUJUAN:\n";
        $systemPrompt .= "Membantu warga mendapatkan informasi layanan publik dengan cepat dan akurat sesuai data resmi.\n\n";
        
        $systemPrompt .= "DATA RESMI & FAQ (Gunakan ini sebagai sumber kebenaran):\n";
        $systemPrompt .= "{$knowledgeBase}\n\n";

        $systemPrompt .= "IDENTITAS PENGGUNA:\n";
        $systemPrompt .= "- Nama: {$userName}\n";
        
        if ($userName === 'Belum diketahui') {
            $systemPrompt .= "- Jika pengguna memberitahu namanya, Anda WAJIB menyapa mereka dengan nama tersebut dan menyertakan tag [SET_NAME:nama] di akhir jawaban.\n";
        } else {
            $systemPrompt .= "- Selalu sapa pengguna dengan nama {$userName} agar percakapan lebih akrab.\n";
        }
        
        $systemPrompt .= "\nBATASAN: Jangan beropini di luar tugas layanan publik. Jika tidak tahu, arahkan untuk menghubungi kantor kecamatan secara langsung.\n";
        $systemPrompt .= "PRIORITAS: Kecepatan > Empati > Akurasi Data.";

        $provider = $profile->ai_provider ?? 'gemini';
        $reply = "Maaf, sedang ada kendala koneksi dengan otak AI saya.";

        try {
            // Persiapkan Pesan dengan Konteks (History)
            if ($provider === 'gemini') {
                $reply = $this->askGemini($profile->google_api_key, $systemPrompt, $message, $history);
            } elseif ($provider === 'openai') {
                $reply = $this->askOpenAI($profile->openai_api_key, $systemPrompt, $message, $history);
            } elseif (in_array($provider, ['deepseek', 'xai', 'openrouter', 'dashscope'])) {
                $reply = $this->askOpenAICompatible($provider, $profile, $systemPrompt, $message, $history);
            } else {
                return null;
            }
            
            // 2. Post-Processing: Deteksi Nama
            if (preg_match('/\[SET_NAME:(.*?)\]/', $reply, $matches)) {
                $detectedName = trim($matches[1]);
                if ($memory && !empty($detectedName)) {
                    $memory->user_name = $detectedName;
                    $userName = $detectedName;
                }
                $reply = str_replace($matches[0], '', $reply);
            }

            // 3. Update Memory Context (Rolling Window - Max 5 Exchanges)
            if ($memory) {
                $history[] = ['role' => 'user', 'content' => $message];
                $history[] = ['role' => 'assistant', 'content' => trim($reply)];
                
                // Simpan hanya 10 item terakhir (5 pasang tanya-jawab)
                $history = array_slice($history, -10);
                
                $memory->context = json_encode($history);
                $memory->save();
            }

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

    private function askGemini($apiKey, $systemPrompt, $message, $history = [])
    {
        if (empty($apiKey)) throw new \Exception("Google API Key belum diisi.");

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
        
        $contents = [];
        
        // Add History to Contents
        foreach ($history as $h) {
            $contents[] = [
                'role' => $h['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $h['content']]]
            ];
        }
        
        // Add Current Message
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]]
        ];

        $response = Http::timeout(15)->post($url, [
            'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
            'contents' => $contents
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Maaf, respon gagal dipahami.";
        }
        throw new \Exception("Gemini Error: " . $response->body());
    }

    private function askOpenAI($apiKey, $systemPrompt, $message, $history = [])
    {
        if (empty($apiKey)) throw new \Exception("OpenAI API Key belum diisi.");

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        
        // Add History
        foreach ($history as $h) {
            $messages[] = ['role' => $h['role'], 'content' => $h['content']];
        }
        
        // Add Current Message
        $messages[] = ['role' => 'user', 'content' => $message];

        $response = Http::withHeaders(['Authorization' => "Bearer {$apiKey}"])
            ->timeout(15)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? "Maaf, respon gagal dipahami.";
        }
        throw new \Exception("OpenAI Error: " . $response->body());
    }

    private function askOpenAICompatible($provider, $profile, $systemPrompt, $message, $history = [])
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

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        
        // Add History
        foreach ($history as $h) {
            $messages[] = ['role' => $h['role'], 'content' => $h['content']];
        }
        
        // Add Current Message
        $messages[] = ['role' => 'user', 'content' => $message];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'HTTP-Referer' => config('app.url'),
        ])->timeout(15)->post($baseUrl, [
            'model' => $model,
            'messages' => $messages
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
