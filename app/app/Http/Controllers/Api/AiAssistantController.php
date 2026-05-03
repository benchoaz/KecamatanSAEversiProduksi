<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppProfile;
use App\Models\AiMemory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantController extends Controller
{
    public function handleChat(Request $request)
    {
        // 1. Validasi Input dari N8N
        $message = $request->input('message');
        $phone = $request->input('phone');
        
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

        // 4. Memori AI (Ingat Nama)
        $userName = 'Belum diketahui';
        $memory = null;
        if ($phone) {
            // Bersihkan nomor telepon
            $phoneClean = preg_replace('/[^0-9]/', '', $phone);
            $memory = AiMemory::firstOrCreate(['phone_number' => $phoneClean]);
            $userName = $memory->user_name ?? 'Belum diketahui';
        }

        // 5. Siapkan Pagar Pembatas (Guardrails & System Prompt)
        $regionName = $profile->region_name ?? 'Kecamatan SAE';
        
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
        
        $systemPrompt .= "KATEGORI LAYANAN YANG HARUS DIPAHAMI:\n";
        $systemPrompt .= "- Administrasi kependudukan (KTP, KK, surat pindah, dll)\n";
        $systemPrompt .= "- Pengaduan masyarakat\n";
        $systemPrompt .= "- Informasi layanan publik\n";
        $systemPrompt .= "- UMKM dan perizinan dasar\n";
        $systemPrompt .= "- Jadwal pelayanan\n\n";
        
        $systemPrompt .= "PENANGANAN KONDISI KHUSUS:\n";
        $systemPrompt .= "[1] KEDARURATAN (Kecelakaan, Bencana, Kebakaran): Respon cepat, singkat, arahkan ke 112.\n";
        $systemPrompt .= "[2] KRIMINAL: Arahkan ke kepolisian (110). Jangan beri analisa hukum.\n";
        $systemPrompt .= "[3] KESEHATAN: Berikan saran umum, arahkan ke puskesmas/RS terdekat.\n\n";
        
        $systemPrompt .= "KONTEKS PENGGUNA SAAT INI:\n";
        $systemPrompt .= "- Nama Pengguna: {$userName}\n";
        $systemPrompt .= "- Nomor HP: " . ($phone ?? 'Tidak tersedia') . "\n";
        
        if ($userName === 'Belum diketahui') {
            $systemPrompt .= "- INSTRUKSI MEMORI: Jika pengguna menyebutkan namanya, Anda WAJIB menyapa mereka dengan nama tersebut di respon ini. SANGAT PENTING: Untuk membantu sistem mengingat, jika user memberitahu namanya, Anda HARUS menyertakan tag [SET_NAME:nama_pengguna] di paling akhir jawaban Anda (contoh: [SET_NAME:Budi]).\n";
        } else {
            $systemPrompt .= "- INSTRUKSI MEMORI: Sapa pengguna dengan nama {$userName} agar terasa lebih personal dan ramah.\n";
        }
        
        $systemPrompt .= "\nBATASAN: Jangan beropini, jangan bercanda, jangan keluar dari konteks pelayanan publik, jangan berikan informasi yang belum pasti.\n";
        $systemPrompt .= "PRIORITAS: Kecepatan respon > Kejelasan informasi > Kepatuhan workflow > Kesopanan";

        $provider = $profile->ai_provider ?? 'gemini';
        $reply = "Maaf, terjadi kesalahan saat menghubungi server AI.";

        try {
            // 6. Routing ke Provider yang dipilih Admin
            if ($provider === 'gemini') {
                $reply = $this->askGemini($profile->google_api_key, $systemPrompt, $message);
            } elseif ($provider === 'openai') {
                $reply = $this->askOpenAI($profile->openai_api_key, $systemPrompt, $message);
            } elseif (in_array($provider, ['deepseek', 'xai', 'openrouter', 'dashscope'])) {
                $reply = $this->askOpenAICompatible($provider, $profile, $systemPrompt, $message);
            } else {
                $reply = "Mohon maaf, model AI (" . strtoupper($provider) . ") belum diimplementasikan.";
            }

            // 7. Post-Processing: Deteksi & Simpan Nama (Memory)
            if (preg_match('/\[SET_NAME:(.*?)\]/', $reply, $matches)) {
                $detectedName = trim($matches[1]);
                if ($memory && !empty($detectedName)) {
                    $memory->user_name = $detectedName;
                    $memory->save();
                    
                    // Log memori baru
                    Log::info("AI Memory Updated: Name '{$detectedName}' stored for phone {$phoneClean}");
                }
                // Hapus tag dari jawaban yang dikirim ke user
                $reply = str_replace($matches[0], '', $reply);
            }

        } catch (\Exception $e) {
            Log::error("AI Webhook Error ({$provider}): " . $e->getMessage());
            $reply = "Mohon maaf, asisten pintar sedang mengalami gangguan koneksi ke pusat data server. Silakan ketik *!menu* untuk melihat panduan manual.";
        }

        // 8. Kembalikan jawaban ke N8N
        return response()->json([
            'reply' => trim($reply),
            'is_ai_active' => true,
            'provider_used' => $provider,
            'user_name' => $userName === 'Belum diketahui' ? null : $userName
        ]);
    }

    private function askGemini($apiKey, $systemPrompt, $message)
    {
        if (empty($apiKey)) throw new \Exception("Google API Key belum diisi di Dashboard.");

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
            'model' => 'gpt-3.5-turbo',
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
