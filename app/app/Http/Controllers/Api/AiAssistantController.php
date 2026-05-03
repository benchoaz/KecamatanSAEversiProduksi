<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantController extends Controller
{
    public function handleChat(Request $request)
    {
        // 1. Validasi Input dari N8N
        $message = $request->input('message');
        if (!$message) {
            return response()->json(['reply' => 'Pesan kosong.'], 400);
        }

        // 2. Ambil Profil Aplikasi & Pengaturan AI
        $profile = AppProfile::first();
        if (!$profile) {
            return response()->json(['reply' => 'Sistem belum dikonfigurasi.']);
        }

        // 3. Cek apakah AI Aktif (Tombol di Dashboard)
        if (!$profile->is_ai_active) {
            return response()->json([
                'reply' => "Mohon maaf, layanan tanya-jawab asisten cerdas sedang dinonaktifkan sementara oleh Admin.\n\nSilakan ketik *!menu* untuk melihat panduan layanan resmi secara manual.",
                'is_ai_active' => false
            ]);
        }

        // 4. Siapkan Pagar Pembatas (Guardrails & System Prompt)
        $regionName = $profile->region_name ?? 'Kecamatan SAE';
        $systemPrompt = "Anda adalah Asisten Virtual Resmi (AI) untuk {$regionName}.\n\n";
        $systemPrompt .= "INSTRUKSI SANGAT PENTING (WAJIB DIPATUHI):\n";
        $systemPrompt .= "1. Tugas Anda HANYA menjawab pertanyaan seputar pelayanan publik, birokrasi, administrasi kependudukan (KTP, KK, Akta, Surat Pindah, SKTM), dan informasi resmi {$regionName}.\n";
        $systemPrompt .= "2. JIKA pengguna bertanya tentang topik di luar tupoksi pemerintahan (seperti: resep makanan, politik, sejarah umum, coding, cuaca, dll), ANDA WAJIB MENOLAKNYA dengan sopan. Contoh: 'Maaf, saya adalah asisten {$regionName}. Saya hanya dapat membantu pertanyaan terkait administrasi dan layanan publik.'\n";
        $systemPrompt .= "3. Gunakan bahasa Indonesia yang ramah, sopan, namun tetap profesional.\n";
        $systemPrompt .= "4. Jawablah dengan singkat dan padat (jangan terlalu panjang karena ini untuk WhatsApp).\n";
        $systemPrompt .= "5. Jika Anda tidak tahu syarat pasti suatu layanan, arahkan pengguna untuk mengetik !menu atau datang langsung ke kantor kecamatan.\n";

        $provider = $profile->ai_provider ?? 'gemini';
        $reply = "Maaf, terjadi kesalahan saat menghubungi server AI. (Provider tidak dikenal)";

        try {
            // 5. Routing ke Provider yang dipilih Admin
            if ($provider === 'gemini') {
                $reply = $this->askGemini($profile->google_api_key, $systemPrompt, $message);
            } elseif ($provider === 'openai') {
                $reply = $this->askOpenAI($profile->openai_api_key, $systemPrompt, $message);
            } elseif (in_array($provider, ['deepseek', 'xai', 'openrouter', 'dashscope'])) {
                $reply = $this->askOpenAICompatible($provider, $profile, $systemPrompt, $message);
            } else {
                $reply = "Mohon maaf, model AI (" . strtoupper($provider) . ") belum diimplementasikan.";
            }
        } catch (\Exception $e) {
            Log::error("AI Webhook Error ({$provider}): " . $e->getMessage());
            $reply = "Mohon maaf, asisten pintar sedang mengalami gangguan koneksi ke pusat data server. Silakan ketik *!menu* untuk melihat panduan manual.";
        }

        // 6. Kembalikan jawaban ke N8N
        return response()->json([
            'reply' => $reply,
            'is_ai_active' => true,
            'provider_used' => $provider
        ]);
    }

    private function askGemini($apiKey, $systemPrompt, $message)
    {
        if (empty($apiKey)) throw new \Exception("Google API Key belum diisi di Dashboard.");

        // Menggunakan model Gemini 1.5 Flash (sangat cepat dan murah)
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
        
        $response = Http::post($url, [
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemPrompt]
                ]
            ],
            'contents' => [
                ['parts' => [['text' => $message]]]
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Maaf, saya tidak bisa memproses pertanyaan tersebut.";
        }

        throw new \Exception("Gemini API Error: " . $response->body());
    }

    private function askOpenAI($apiKey, $systemPrompt, $message)
    {
        if (empty($apiKey)) throw new \Exception("OpenAI API Key belum diisi di Dashboard.");

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo', // Model default
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message],
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? "Maaf, respon tidak dapat dipahami.";
        }

        throw new \Exception("OpenAI API Error: " . $response->body());
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
            'HTTP-Referer' => config('app.url', 'https://kecamatanbesuk.web.id'),
        ])->post($baseUrl, [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message],
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? "Maaf, respon tidak dapat dipahami.";
        }

        throw new \Exception("{$provider} API Error: " . $response->body());
    }
}
