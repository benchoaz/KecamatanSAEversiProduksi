<?php

namespace App\Http\Controllers\Api;

use App\Models\Berita;
use App\Models\PublicService;
use App\Models\Umkm;
use App\Models\JobVacancy;
use App\Models\WorkDirectory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppProfile;
use App\Models\AiMemory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AiAssistantController extends Controller
{
    /**
     * Get real-time context (News & Stats) for AI
     */
    private function getAiContext()
    {
        return Cache::remember('ai_context_data', 1800, function() {
            // 1. Get Latest News
            $news = Berita::published()
                ->orderBy('published_at', 'desc')
                ->take(3)
                ->get(['judul', 'ringkasan', 'published_at']);
            
            $newsText = "";
            foreach($news as $n) {
                $date = $n->published_at->format('d M Y');
                $newsText .= "- [{$date}] {$n->judul}: {$n->ringkasan}\n";
            }

            // 2. Get Statistics (Aggregated from 17 Villages)
            $desas = \App\Models\Desa::all();
            
            $totalPenduduk = $desas->sum('jumlah_penduduk');
            $totalLaki = $desas->sum('jumlah_laki_laki');
            $totalPerempuan = $desas->sum('jumlah_perempuan');
            $totalKK = $desas->sum('jumlah_kk');
            $villageNames = $desas->pluck('nama_desa')->join(', ');
            
            $statsText = "STATISTIK WILAYAH (AGREGAT 17 DESA):\n";
            $statsText .= "- Daftar 17 Desa: {$villageNames}\n";
            $statsText .= "- Total Penduduk: {$totalPenduduk} jiwa ({$totalLaki} Laki-laki, {$totalPerempuan} Perempuan)\n";
            $statsText .= "- Total Kepala Keluarga (KK): {$totalKK}\n";
            
            $serviceStats = [
                'total_layanan' => PublicService::count(),
                'layanan_selesai' => PublicService::where('status', PublicService::STATUS_SELESAI)->count(),
                'rating' => round(PublicService::whereNotNull('rating')->avg('rating') ?? 0, 1),
                'total_umkm' => Umkm::count(),
                'total_loker' => JobVacancy::count(),
            ];

            $statsText .= "\nSTATISTIK LAYANAN DIGITAL:\n";
            $statsText .= "- Total Layanan Publik: {$serviceStats['total_layanan']}\n";
            $statsText .= "- Layanan Selesai: {$serviceStats['layanan_selesai']}\n";
            $statsText .= "- Kepuasan: {$serviceStats['rating']}/5.0\n";
            $statsText .= "- Total UMKM: {$serviceStats['total_umkm']}, Loker: {$serviceStats['total_loker']}\n";

            return [
                'news' => $newsText ?: "Belum ada berita terbaru.",
                'stats' => $statsText
            ];
        });
    }

    public function handleChat(Request $request)
    {
        $message = $request->input('message');
        $phone = $request->input('phone');
        
        if (!$message) {
            return response()->json(['reply' => 'Pesan kosong.'], 400);
        }

        $profile = AppProfile::first();
        if (!$profile) {
            return response()->json(['reply' => 'Sistem belum dikonfigurasi.']);
        }

        if (!$profile->is_ai_active) {
            return response()->json([
                'reply' => "Mohon maaf, layanan asisten cerdas sedang dinonaktifkan sementara.",
                'is_ai_active' => false
            ]);
        }

        // Memori AI
        $userName = 'Belum diketahui';
        $memory = null;
        if ($phone) {
            $phoneClean = preg_replace('/[^0-9]/', '', $phone);
            $memory = AiMemory::firstOrCreate(['phone_number' => $phoneClean]);
            $userName = $memory->user_name ?? 'Belum diketahui';
        }

        $regionName = $profile->full_region_name ?? ucwords($profile->region_name ?? 'Kecamatan SAE');
        $botName = !empty($profile->ai_bot_name) ? $profile->ai_bot_name : 'SAE-Bot';
        $botInstruction = !empty($profile->ai_bot_instruction) ? $profile->ai_bot_instruction : '';
        
        // WAKTU SEKARANG
        $now = Carbon::now('Asia/Jakarta');
        $timeNow = $now->format('H.i');

        // SYSTEM PROMPT
        $systemPrompt = "IDENTITAS PENTING:\n";
        $systemPrompt .= "- Nama Anda: '{$botName}'\n";
        $systemPrompt .= "- Wilayah Anda: {$regionName}\n";
        $systemPrompt .= "- Waktu Sekarang: {$timeNow} WIB (PENTING: Gunakan waktu ini sebagai satu-satunya acuan waktu saat ini)\n\n";
        
        $systemPrompt .= "ATURAN MUTLAK:\n";
        $systemPrompt .= "- DILARANG KERAS menyebut nama 'Besuk' dengan huruf kecil. Selalu gunakan 'Besuk'.\n";
        $systemPrompt .= "- Anda adalah asisten virtual resmi yang sangat ramah, hangat, dan penuh empati dari {$regionName}.\n\n";

        $systemPrompt .= "⏰ LOGIKA SALAM (WAJIB IKUTI):\n";
        $systemPrompt .= "- 04.00–10.59 → Selamat pagi\n";
        $systemPrompt .= "- 11.00–14.59 → Selamat siang\n";
        $systemPrompt .= "- 15.00–17.59 → Selamat sore\n";
        $systemPrompt .= "- 18.00–03.59 → Selamat malam\n\n";

        $systemPrompt .= "🧠 LOGIKA UTAMA MANAJEMEN NAMA:\n";
        $systemPrompt .= "1. Jika Nama Saat Ini adalah 'Belum diketahui', Anda WAJIB mendeteksi nama dari pesan atau BERTANYA NAMA dengan sangat ramah.\n";
        $systemPrompt .= "2. Jika terdeteksi, simpan dengan tag [SET_NAME:nama].\n";
        $systemPrompt .= "3. Jika Nama Saat Ini sudah berisi nama asli (bukan 'Belum diketahui'), sapa langsung dengan nama tersebut secara hangat.\n";
        $systemPrompt .= "4. JANGAN PERNAH menyapa user dengan sebutan 'Kak Belum diketahui' atau 'Pak Belum diketahui'.\n\n";

        $systemPrompt .= "🎯 PERILAKU BERDASARKAN KONDISI (IKUTI SECARA KAKU):\n";
        $systemPrompt .= "1. PESAN PERTAMA & TIDAK ADA NAMA:\n";
        $systemPrompt .= "   - WAJIB: Berikan salam sesuai waktu, perkenalkan diri, dan TANYA NAMA secara hangat.\n";
        $systemPrompt .= "   - CONTOH RAMAH: 'Halo! Selamat sore! 👋 Perkenalkan, saya BoT SAE, asisten digital Bapak/Ibu di {$regionName}. Supaya kita bisa lebih akrab, kalau boleh tahu dengan Bapak/Ibu/Kakak siapa ya saya sekarang sedang berkomunikasi? 😊'\n";
        $systemPrompt .= "2. PESAN LANJUTAN / ADA NAMA:\n";
        $systemPrompt .= "   - Sapa dengan nama ({$userName}) dan bantu kebutuhan mereka secara hangat.\n\n";

        $systemPrompt .= "🎤 GAYA BAHASA:\n";
        $systemPrompt .= "- Sangat sopan, sangat ramah, natural (seperti manusia), tidak robotik.\n";
        $systemPrompt .= "- Gunakan sapaan 'Bapak/Ibu/Kakak/Adik' sesuai kesopanan.\n";
        $systemPrompt .= "- Gunakan emoji (👋, 😊, 🌤️) secukupnya (maksimal 2).\n\n";

        $systemPrompt .= "PERINTAH KHUSUS:\n";
        $systemPrompt .= "- JANGAN PERNAH memberikan jawaban template yang kaku. Jadilah asisten yang melayani dengan tulus.\n";
        $systemPrompt .= "- Jika warga mengadu/melapor, tunjukkan empati dalam, lalu arahkan ke: " . $this->getPublicUrl() . "/#pengaduan\n";
        $systemPrompt .= "- Jika warga mencari Loker/UMKM/Jasa, arahkan ke Pusat Ekonomi {$regionName} di: " . $this->getPublicUrl() . "/ekonomi\n\n";

        if (!empty($botInstruction)) {
            $systemPrompt .= "\nINSTRUKSI TAMBAHAN ADMIN:\n" . $botInstruction . "\n";
        }
        
        $context = $this->getAiContext();
        $systemPrompt .= "\nINFORMASI TERKINI & BERITA:\n" . $context['news'] . "\n";
        $systemPrompt .= "\n" . $context['stats'] . "\n";

        $systemPrompt .= "\nATURAN PENUTUP: Selalu akhiri dengan arahan navigasi (Ketik MENU, STATUS, dsb).\n";

        $provider = $profile->ai_provider ?? 'gemini';
        $reply = "Maaf, terjadi kesalahan.";

        try {
            if ($provider === 'gemini') {
                $reply = $this->askGemini($profile->google_api_key, $systemPrompt, $message);
            } elseif ($provider === 'openai') {
                $reply = $this->askOpenAI($profile->openai_api_key, $systemPrompt, $message);
            } elseif (in_array($provider, ['deepseek', 'xai', 'openrouter', 'dashscope'])) {
                $reply = $this->askOpenAICompatible($provider, $profile, $systemPrompt, $message);
            }

            if (preg_match('/\[SET_NAME:(.*?)\]/', $reply, $matches)) {
                $detectedName = trim($matches[1]);
                if ($memory && !empty($detectedName)) {
                    $memory->user_name = $detectedName;
                    $memory->save();
                }
                $reply = str_replace($matches[0], '', $reply);
            }

        } catch (\Exception $e) {
            Log::error("AI Webhook Error ({$provider}): " . $e->getMessage());
            $reply = "Mohon maaf, sistem sedang gangguan. Silakan ketik *MENU*.";
        }

        return response()->json([
            'reply' => trim($reply),
            'is_ai_active' => true,
            'user_name' => $userName === 'Belum diketahui' ? null : $userName
        ]);
    }

    private function askGemini($apiKey, $systemPrompt, $message)
    {
        if (empty($apiKey)) throw new \Exception("API Key kosong.");
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
        $response = Http::post($url, [
            'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
            'contents' => [['parts' => [['text' => $message]]]]
        ]);
        if ($response->successful()) {
            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Maaf, gagal proses.";
        }
        throw new \Exception("Gemini Error: " . $response->body());
    }

    private function askOpenAI($apiKey, $systemPrompt, $message)
    {
        if (empty($apiKey)) throw new \Exception("API Key kosong.");
        $response = Http::withHeaders(['Authorization' => "Bearer {$apiKey}"])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message],
                ]
            ]);
        if ($response->successful()) {
            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? "Maaf, gagal proses.";
        }
        throw new \Exception("OpenAI Error: " . $response->body());
    }

    private function askOpenAICompatible($provider, $profile, $systemPrompt, $message)
    {
        $apiKey = ''; $baseUrl = ''; $model = '';
        if ($provider === 'deepseek') { $apiKey = $profile->deepseek_api_key; $baseUrl = 'https://api.deepseek.com/chat/completions'; $model = 'deepseek-chat'; }
        elseif ($provider === 'xai') { $apiKey = $profile->xai_api_key; $baseUrl = 'https://api.x.ai/v1/chat/completions'; $model = 'grok-beta'; }
        elseif ($provider === 'openrouter') { $apiKey = $profile->openrouter_api_key; $baseUrl = 'https://openrouter.ai/api/v1/chat/completions'; $model = 'google/gemini-flash-1.5'; }
        elseif ($provider === 'dashscope') { $apiKey = $profile->dashscope_api_key; $baseUrl = 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions'; $model = 'qwen-turbo'; }

        if (empty($apiKey)) throw new \Exception("API Key kosong.");
        $response = Http::withHeaders(['Authorization' => "Bearer {$apiKey}"])
            ->post($baseUrl, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message],
                ]
            ]);
        if ($response->successful()) {
            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? "Maaf, gagal proses.";
        }
        throw new \Exception("Error: " . $response->body());
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
