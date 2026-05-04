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
     */
    public function handle(string $phone, string $message): ?array
    {
        try {
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
                
                if (!empty($memory->context)) {
                    $history = json_decode($memory->context, true) ?: [];
                }
            }

            $regionName = $profile->full_region_name ?? 'Kecamatan SAE';
            $officeAddress = $profile->address ?? 'Kantor Kecamatan';
            $officePhone = $profile->phone ?? '-';
            $knowledgeBase = $this->getDynamicKnowledge();
            
            // PROMPT MANUSIAWI & EMPATI
            $systemPrompt = "IDENTITAS PENTING:\n";
            $systemPrompt .= "- Nama Anda: 'SAE-Bot'\n";
            $systemPrompt .= "- Wilayah Anda: {$regionName}\n";
            $systemPrompt .= "- Alamat Kantor: {$officeAddress}\n";
            $systemPrompt .= "- Kontak Kantor: {$officePhone}\n\n";
            
            $systemPrompt .= "ATURAN MUTLAK:\n";
            $systemPrompt .= "- DILARANG KERAS menyebut nama 'Besuk' atau wilayah lain selain {$regionName}.\n";
            $systemPrompt .= "- Anda adalah asisten virtual resmi yang ramah, cerdas, dan penuh empati dari {$regionName}.\n\n";
            
            $systemPrompt .= "KEPRIBADIAN & ATURAN PERKENALAN:\n";
            $systemPrompt .= "- Anda adalah sosok pelayan masyarakat yang tulus dan sopan.\n";
            $systemPrompt .= "- Jika ini adalah interaksi pertama dan Nama Pengguna masih 'Belum diketahui', Anda WAJIB memperkenalkan diri dulu sebagai SAE-Bot dan bertanya nama mereka dengan sangat sopan.\n";
            $systemPrompt .= "- Contoh: 'Halo! Saya SAE-Bot, asisten digital Bapak/Ibu di {$regionName}. Kalau boleh tahu, dengan Bapak/Ibu siapa saya berbicara?'\n";
            $systemPrompt .= "- Jika sudah tahu namanya ({$userName}), sapa mereka dengan akrab dan jangan bertanya nama lagi.\n";
            $systemPrompt .= "- Gunakan sapaan hangat seperti 'Bapak/Ibu', 'Kakak', atau 'Mas/Mbak' sesuai konteks.\n";
            $systemPrompt .= "- Jawaban Anda harus natural, tidak kaku, dan mengutamakan pelayanan.\n\n";
            
            $systemPrompt .= "PERINTAH KHUSUS & KONTROL:\n";
            $systemPrompt .= "- Jika warga ingin mengadu atau melapor (kata: 'ngadu', 'lapor', 'pengaduan'), tunjukkan empati yang sangat dalam terlebih dahulu. Dengarkan keluhan mereka dengan hangat, kemudian sampaikan dengan sangat sopan bahwa agar laporan mereka bisa dipantau dan ditindaklanjuti secara resmi oleh tim kecamatan, mereka WAJIB mengisi formulir di link berikut: " . $this->getPublicUrl() . "/#pengaduan\n";
            $systemPrompt .= "- Jika warga mencari Pekerjaan, Loker, UMKM, atau Jasa (seperti: ojek, tukang pijat, dll), Anda HARUS mengacu pada 'Pusat Ekonomi {$regionName}' di: " . $this->getPublicUrl() . "/ekonomi\n";
            $systemPrompt .= "- DILARANG KERAS menyarankan warga mencari di Google, Gojek, Grab, atau media online luar lainnya. Fokuslah hanya pada layanan internal kecamatan.\n";
            $systemPrompt .= "- Jika data produk/jasa/loker yang dicari tidak ada di database Anda, jawab dengan sangat hangat: 'Mohon maaf Bapak/Ibu, saat ini data [Nama Barang/Jasa/Pekerjaan] belum tersedia di direktori Pusat Ekonomi {$regionName} kami. Kami akan terus memperbarui data kami demi kenyamanan warga.'\n";
            $systemPrompt .= "- Jika warga ingin membatalkan/berhenti (kata: 'batal', 'stop', 'berhenti'), jawab dengan sopan bahwa proses dihentikan.\n";
            $systemPrompt .= "- Jika warga ingin melanjutkan (kata: 'teruskan', 'lanjut'), berikan semangat dan lanjutkan bantuan Anda.\n";
            $systemPrompt .= "- Anda harus memahami percakapan sederhana dan salam (halo, apa kabar, terima kasih) dengan ramah.\n\n";

            $systemPrompt .= "TUJUAN:\n";
            $systemPrompt .= "Membantu warga mendapatkan informasi layanan publik dengan cepat dan akurat sesuai data resmi.\n\n";
            
            $systemPrompt .= "DATA RESMI & FAQ (Gunakan ini sebagai sumber kebenaran):\n";
            $systemPrompt .= "{$knowledgeBase}\n\n";

            $systemPrompt .= "IDENTITAS PENGGUNA:\n";
            $systemPrompt .= "- Nama Saat Ini: {$userName}\n";
            
            if ($userName === 'Belum diketahui') {
                $systemPrompt .= "- Jika pengguna memberitahu namanya, Anda wajib menyapa mereka dengan nama tersebut di kalimat berikutnya dan menyertakan tag [SET_NAME:nama] di akhir jawaban.\n";
            } else {
                $systemPrompt .= "- Selalu gunakan nama {$userName} dalam percakapan agar terasa lebih personal.\n";
            }
            
            $systemPrompt .= "\nSTRATEGI JAWABAN:\n";
            $systemPrompt .= "- Selalu perhatikan urutan percakapan. Jika warga bertanya 'Alamatnya mana' setelah membahas layanan (seperti KK), arahkan ke Alamat Kantor Kecamatan: {$officeAddress}.\n";
            $systemPrompt .= "- Jangan beropini di luar tugas layanan publik. Jika tidak tahu, arahkan untuk menghubungi kantor kecamatan secara langsung.\n";
            $systemPrompt .= "PRIORITAS: Konteks Terbaru > Empati > Akurasi Data.";

            $provider = $profile->ai_provider ?? 'gemini';
            $reply = "";

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
            
            // Post-Processing: Deteksi Nama
            if (preg_match('/\[SET_NAME:(.*?)\]/', $reply, $matches)) {
                $detectedName = trim($matches[1]);
                if ($memory && !empty($detectedName)) {
                    $memory->user_name = $detectedName;
                    $userName = $detectedName;
                }
                $reply = str_replace($matches[0], '', $reply);
            }

            // Update Memory Context
            if ($memory) {
                $history[] = ['role' => 'user', 'content' => $message];
                $history[] = ['role' => 'assistant', 'content' => trim($reply)];
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
            Log::error("WhatsApp AI Error: " . $e->getMessage());
            return null; 
        }
    }

    private function askGemini($apiKey, $systemPrompt, $message, $history = [])
    {
        if (empty($apiKey)) throw new \Exception("Google API Key belum diisi.");

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
        $contents = [];
        foreach ($history as $h) {
            $contents[] = [
                'role' => $h['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $h['content']]]
            ];
        }
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
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Aduh, maaf ya Kak, otak saya lagi sedikit macet. Bisa diulang pertanyaannya?";
        }
        throw new \Exception("Gemini Error: " . $response->body());
    }

    private function askOpenAI($apiKey, $systemPrompt, $message, $history = [])
    {
        if (empty($apiKey)) throw new \Exception("OpenAI API Key belum diisi.");

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($history as $h) {
            $messages[] = ['role' => $h['role'], 'content' => $h['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $response = Http::withHeaders(['Authorization' => "Bearer {$apiKey}"])
            ->timeout(15)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? "Aduh, maaf ya Kak, otak saya lagi sedikit macet. Bisa diulang pertanyaannya?";
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
        foreach ($history as $h) {
            $messages[] = ['role' => $h['role'], 'content' => $h['content']];
        }
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
            return $data['choices'][0]['message']['content'] ?? "Aduh, maaf ya Kak, otak saya lagi sedikit macet. Bisa diulang pertanyaannya?";
        }
        throw new \Exception("{$provider} Error: " . $response->body());
    }

    private function getDynamicKnowledge(): string
    {
        return Cache::remember('whatsapp_ai_knowledge', 600, function() {
            $knowledge = "DAFTAR LAYANAN TERSEDIA:\n";
            $nodes = ServiceNode::where('is_active', true)->get();
            foreach ($nodes as $node) {
                $knowledge .= "- " . strtoupper($node->name) . ": " . ($node->description ?? 'Layanan administrasi') . "\n";
                // Fixed: node_id instead of service_node_id, and removed non-existent is_active
                $requirements = ServiceRequirement::where('node_id', $node->id)->get();
                if ($requirements->count() > 0) {
                    // Fixed: label instead of name
                    $knowledge .= "  Persyaratan: " . $requirements->pluck('label')->implode(', ') . ".\n";
                }
            }
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

    protected function getPublicUrl(): string
    {
        $profile = AppProfile::first();
        if ($profile && !empty($profile->public_url)) {
            return rtrim($profile->public_url, '/');
        }
        return rtrim(env('PUBLIC_BASE_URL', config('app.url')), '/');
    }
}
