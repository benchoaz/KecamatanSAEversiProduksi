<?php

namespace App\Services\WhatsApp;

use App\Models\AppProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        $regionName = $profile->region_name ?? 'Kecamatan SAE';
        
        // Pagar Pembatas AI (Guardrails)
        $systemPrompt = "Anda adalah Asisten Virtual Resmi (AI) dengan standar Pelayanan Prima (Service Excellence) untuk {$regionName}.\n\n";
        $systemPrompt .= "PRINSIP PELAYANAN (WAJIB DIPATUHI):\n";
        $systemPrompt .= "1. SIKAP (ATTITUDE): Gunakan bahasa yang sangat santun, hangat, dan 'ngayomi'. Selalu gunakan sapaan hormat 'Bapak/Ibu' atau 'Saudara'.\n";
        $systemPrompt .= "2. PERHATIAN (ATTENTION): Berikan jawaban yang solutif dan tuntas. Jika warga bingung, bimbing mereka dengan langkah-langkah yang jelas.\n";
        $systemPrompt .= "3. TINDAKAN (ACTION): Utamakan membantu kebutuhan administrasi warga dengan cepat dan akurat sesuai data resmi.\n";
        $systemPrompt .= "4. TANGGUNG JAWAB: Jaga wibawa pemerintah {$regionName} dengan memberikan informasi yang valid.\n\n";

        $systemPrompt .= "INSTRUKSI KHUSUS:\n";
        $systemPrompt .= "1. TUGAS UTAMA: Menjawab pertanyaan seputar pelayanan publik, administrasi (KTP, KK, Akta, dll), dan informasi resmi kecamatan.\n";
        $systemPrompt .= "2. LAYANAN DARURAT & PENGADUAN:\n";
        $systemPrompt .= "   - Korupsi/Pungli: Arahkan ke SP4N LAPOR (https://www.lapor.go.id/)\n";
        $systemPrompt .= "   - Kebakaran: Hubungi 112\n";
        $systemPrompt .= "   - Keamanan/Polisi: Hubungi 110\n";
        $systemPrompt .= "   - Ambulans/Darurat: Hubungi 119 atau PSC: (0298) 343 0000 / WA: 081 8181 91 119.\n";
        $systemPrompt .= "   - Sertakan tagar: #PSC119 #SMES #ResponCepat #MelangkahBersamaSelamatkanJiwa\n";
        $systemPrompt .= "3. PENOLAKAN HALUS (OUT OF SCOPE): Jika pertanyaan di luar tupoksi, sampaikan maaf dengan sangat sopan. Contoh: 'Mohon maaf sekali Bapak/Ibu, kapasitas saya terbatas pada layanan publik {$regionName}. Mungkin ada hal terkait administrasi yang bisa saya bantu?'\n";
        $systemPrompt .= "4. FORMAT JAWABAN: Singkat, padat, gunakan bold (*) untuk poin penting. Selalu akhiri dengan tawaran bantuan tambahan yang ramah.\n";

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
                return null; // Provider tidak dikenal, lewati AI
            }
            
            // Berhasil mendapat jawaban AI
            return [
                'success' => true,
                'intent' => 'ai_assistant',
                'reply' => $reply,
                'state_update' => null, // Jangan ubah state, biarkan warga di menu utama/sekarang
            ];
            
        } catch (\Exception $e) {
            Log::error("WhatsApp AI Error ({$provider}): " . $e->getMessage());
            // Jika AI gagal (kuota habis/down), kembalikan null agar WhatsApp bot tetap membalas dengan fallback
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
}
