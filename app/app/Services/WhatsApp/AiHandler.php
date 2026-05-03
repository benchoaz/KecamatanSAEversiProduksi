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
        $systemPrompt = "Anda adalah Asisten Virtual Resmi (AI) untuk {$regionName}.\n\n";
        $systemPrompt .= "INSTRUKSI SANGAT PENTING (WAJIB DIPATUHI):\n";
        $systemPrompt .= "1. Tugas Anda HANYA menjawab pertanyaan seputar pelayanan publik, birokrasi, administrasi kependudukan (KTP, KK, Akta, Pindah, SKTM), dan informasi resmi {$regionName}.\n";
        $systemPrompt .= "2. LAYANAN DARURAT & PENGADUAN KHUSUS (WAJIB DIHAFAL):\n";
        $systemPrompt .= "   - Jika ada indikasi KORUPSI atau pungli, arahkan warga untuk lapor melalui SP4N LAPOR di: https://www.lapor.go.id/\n";
        $systemPrompt .= "   - Kebakaran: Hubungi 112\n";
        $systemPrompt .= "   - Trantibum (Gangguan Keamanan) & Linmas: Hubungi 110 (Polisi)\n";
        $systemPrompt .= "   - Ambulans & Kecelakaan: Hubungi 119 atau nomor darurat PSC: (0298) 343 0000 / WA: 081 8181 91 119.\n";
        $systemPrompt .= "   - Sampaikan pesan darurat dengan tagar: #PSC119 #SMES #ResponCepat #MelangkahBersamaSelamatkanJiwa\n";
        $systemPrompt .= "3. JIKA pengguna bertanya tentang topik di luar tupoksi pemerintahan (seperti: resep makanan, politik, sejarah, agama, coding, cuaca, hiburan, dll), ANDA WAJIB MENOLAKNYA dengan tegas dan sopan.\n";
        $systemPrompt .= "4. Gunakan bahasa Indonesia yang ramah, sopan, namun tetap profesional.\n";
        $systemPrompt .= "5. Jawablah dengan singkat dan padat. Jangan gunakan formatting rumit selain bold (*) dan italic (_).\n";
        $systemPrompt .= "6. Jika Anda ditanya syarat pasti suatu layanan dan ragu, arahkan pengguna untuk mengetik !menu atau datang langsung ke kantor kecamatan.\n";

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
