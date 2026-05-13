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
use App\Models\Berita;
use App\Models\PublicService;
use App\Models\Umkm;
use App\Models\JobVacancy;
use App\Models\Desa;
use Carbon\Carbon;

class AiHandler
{
    protected \App\Services\WeatherService $weatherService;
    protected \App\Models\MasterLayanan $masterLayanan;

    public function __construct(\App\Services\WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

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
                $userName = (!empty($memory->user_name)) ? $memory->user_name : 'Belum diketahui';
                
                if (!empty($memory->context)) {
                    $history = json_decode($memory->context, true) ?: [];
                }
            }

            $regionName    = $profile->full_region_name ?? ucwords($profile->region_name ?? 'Kecamatan SAE');
            $officeAddress = $profile->address ?? 'Kantor Kecamatan';
            $officePhone   = $profile->phone ?? '-';
            $botName       = !empty($profile->ai_bot_name) ? $profile->ai_bot_name : 'SAE-Bot';
            $botInstruction = !empty($profile->ai_bot_instruction) ? $profile->ai_bot_instruction : '';
            $knowledgeBase = $this->getDynamicKnowledge();
            
            // WAKTU SEKARANG (Server Time)
            $now = Carbon::now('Asia/Jakarta');
            $timeNow = $now->format('H.i');
            $dateNow = $now->translatedFormat('l, d F Y');
            
            // Tentukan Salam Berdasarkan Waktu
            $hour = (int)$now->format('H');
            $minute = (int)$now->format('i');
            $totalMinutes = ($hour * 60) + $minute;

            $greeting = 'malam';
            if ($totalMinutes >= 240 && $totalMinutes < 660) $greeting = 'pagi';      // 04:00 - 10:59
            elseif ($totalMinutes >= 660 && $totalMinutes < 900) $greeting = 'siang'; // 11:00 - 14:59
            elseif ($totalMinutes >= 900 && $totalMinutes < 1110) $greeting = 'sore'; // 15:00 - 18:29
            else $greeting = 'malam';                                                 // 18:30 - 03:59

            // PROMPT DINAMIS & CERDAS
            $systemPrompt = "IDENTITAS PENTING:\n";
            $systemPrompt .= "- Nama Anda: '{$botName}'\n";
            $systemPrompt .= "- Wilayah Anda: {$regionName}\n";
            $systemPrompt .= "- Alamat Kantor: {$officeAddress}\n";
            $systemPrompt .= "- Kontak Kantor: {$officePhone}\n";
            $systemPrompt .= "- Hari & Tanggal Sekarang: {$dateNow}\n";
            $systemPrompt .= "- Waktu Sekarang: {$timeNow} WIB\n";
            $systemPrompt .= "- Salam Saat Ini: Selamat {$greeting} (GUNAKAN SALAM INI!)\n\n";
            
            $systemPrompt .= "🚨 ATURAN PRIORITAS TERTINGGI (WAJIB):\n";
            $systemPrompt .= "1. DILARANG KERAS MENGGUNAKAN FORMAT MARKDOWN (seperti [teks](url) atau [url]).\n";
            $systemPrompt .= "2. TULIS URL/LINK APA ADANYA SECARA MENTAH. Contoh yang SALAH: [https://google.com]. Contoh yang BENAR: https://google.com\n";
            $systemPrompt .= "3. JANGAN pernah menggunakan kurung siku [] atau kurung biasa () untuk membungkus link.\n";
            $systemPrompt .= "4. Jika Anda melanggar aturan link ini, informasi Anda tidak akan bisa dibaca oleh warga.\n\n";

            $systemPrompt .= "🔴 ATURAN UMUM:\n";
            $systemPrompt .= "1. Gunakan HANYA URL yang tertera di DATA RESMI di bawah.\n";
            $systemPrompt .= "2. DILARANG menyebut nama 'Besuk' dengan huruf kecil. Selalu gunakan 'Besuk'.\n";
            $systemPrompt .= "3. Anda adalah asisten virtual resmi yang sangat ramah dari {$regionName}.\n\n";

            $systemPrompt .= "📝 FORMAT JAWABAN LAYANAN (WAJIB TIRU):\n";
            $systemPrompt .= "Tentu Bapak/Ibu [Nama], untuk mengurus [Layanan], berikut syaratnya:\n- Syarat 1\n- Syarat 2\n\nEstimasi Selesai: [Waktu]\n\nSilakan ajukan melalui link resmi berikut:\nURL_RESMI_PENGAJUAN\n\n";

            $systemPrompt .= "👤 PROFIL PENGGUNA (DATA SISTEM - PRIORITAS TINGGI):\n";
            $systemPrompt .= ">>> NAMA TERDAFTAR : '{$userName}'\n";
            $systemPrompt .= ">>> ID KONTAK (WA) : '{$phone}'\n\n";
            
            $systemPrompt .= "🧠 LOGIKA MANAJEMEN IDENTITAS:\n";
            $systemPrompt .= "1. Nama resmi user adalah '{$userName}'. JANGAN PERNAH memanggil dengan angka ID/Nomor WA.\n";
            $systemPrompt .= "2. Jika Nama = 'Belum diketahui', sapalah dengan 'Bapak/Ibu' secara umum. DILARANG bertanya 'Ini dengan siapa?' atau 'Boleh tahu namanya?' kecuali user yang memulai topik perkenalan.\n";
            $systemPrompt .= "3. Jika User menyebutkan namanya secara sukarela (Contoh: 'Halo, saya Budi'), Anda WAJIB memberikan tag [SET_NAME:Budi] di akhir jawaban Anda.\n";
            $systemPrompt .= "4. ⚠️ PERINGATAN KERAS: Angka panjang (NIK/No.HP) BUKAN nama. Jangan pernah menganggap angka sebagai identitas panggilan.\n";
            $systemPrompt .= "5. Jika Nama SUDAH ADA di data sistem, sapalah dengan 'Bapak [Nama]' atau 'Ibu [Nama]' secara ramah dan akrab.\n\n";

            $systemPrompt .= "🎤 GAYA BAHASA:\n";
            $systemPrompt .= "- Sangat sopan, sangat ramah, natural (seperti manusia).\n";
            $systemPrompt .= "- Gunakan emoji (👋, 😊, 🌤️, 🌙) secara natural.\n\n";

            $systemPrompt .= "PERINTAH KHUSUS LAINNYA:\n";
            $systemPrompt .= "- Jika warga ingin LAPOR/MENGADU: Berikan link pengaduan: " . $this->getPublicUrl() . "/#pengaduan\n";
            $systemPrompt .= "- Jika warga mencari JASA, UMKM, MAKANAN, KULINER, atau merasa LAPAR: Arahkan ke Pusat Ekonomi {$regionName} di: " . $this->getPublicUrl() . "/ekonomi\n";
            $systemPrompt .= "- Jika warga bertanya CUACA: Gunakan data resmi BMKG di bawah.\n\n";

            $systemPrompt .= "📚 DATA RESMI (SUMBER INFORMASI TUNGGAL):\n";
            $systemPrompt .= "{$knowledgeBase}\n\n";
            
            $systemPrompt .= "ATURAN PENUTUP (WAJIB):\n";
            $systemPrompt .= "- Setiap jawaban HARUS ditutup dengan arahan navigasi (contoh: Ketik MENU untuk layanan lain).\n\n";

            if (!empty($botInstruction)) {
                $systemPrompt .= "\n\nINSTRUKSI TAMBAHAN ADMIN:\n" . $botInstruction;
            }

            // DEBUG LOG: See what is sent to AI
            Log::info("WA_AI_PROMPT_SENT", [
                'phone' => $phone,
                'message' => $message,
                'system_prompt_length' => strlen($systemPrompt),
                'knowledge_base_preview' => substr($knowledgeBase, 0, 100)
            ]);

            $provider = $profile->ai_provider ?? 'gemini';
            $reply = "";

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
                // Jaring Pengaman: Jika nama hanya berisi angka, abaikan (mungkin AI salah tangkap NIK/HP)
                if ($memory && !empty($detectedName) && !is_numeric($detectedName)) {
                    $memory->user_name = $detectedName;
                    $userName = $detectedName;
                    $memory->save();
                    \Log::info("AI SET NAME DETECTED: " . $detectedName);
                }
                $reply = str_replace($matches[0], '', $reply);
            }
            
            // Fallback Name Detection is DISABLED because it causes hallucinations to be saved as real names.
            // Only SET_NAME tag is allowed to change the name.

            // Garansi Navigasi
            $replyLower = strtolower($reply);
            $navKeywords = ['menu', 'status', 'cek', 'lacak', 'ketik', 'pilih nomor', 'pilih angka', 'layanan'];
            $hasNav = false;
            foreach ($navKeywords as $kw) {
                if (str_contains($replyLower, $kw)) {
                    $hasNav = true;
                    break;
                }
            }

            if (!$hasNav) {
                $reply = trim($reply) . "\n\nKetik *MENU* untuk melihat daftar layanan kami. 😊";
            }

            // ANTI-HALLUCINATION SHIELD (KTP 14-day fix)
            if (str_contains($reply, '14')) {
                $ktpService = \App\Models\MasterLayanan::where('nama_layanan', 'like', '%KTP%')->first();
                if ($ktpService && $ktpService->estimasi_waktu) {
                    $reply = str_replace(['14 Hari Kerja', '14 hari kerja', '14 Hari', '14 hari'], $ktpService->estimasi_waktu, $reply);
                }
            }

            // Update Memory
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
                'model' => 'gpt-4o-mini',
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
        $apiKey = ''; $baseUrl = ''; $model = '';
        if ($provider === 'deepseek') { $apiKey = $profile->deepseek_api_key; $baseUrl = 'https://api.deepseek.com/chat/completions'; $model = 'deepseek-chat'; }
        elseif ($provider === 'xai') { $apiKey = $profile->xai_api_key; $baseUrl = 'https://api.x.ai/v1/chat/completions'; $model = 'grok-beta'; }
        elseif ($provider === 'openrouter') { $apiKey = $profile->openrouter_api_key; $baseUrl = 'https://openrouter.ai/api/v1/chat/completions'; $model = 'google/gemini-flash-1.5'; }
        elseif ($provider === 'dashscope') { $apiKey = $profile->dashscope_api_key; $baseUrl = 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions'; $model = 'qwen-turbo'; }

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
        // Disable cache temporarily to ensure fresh data for 3-day KTP fix
        // return Cache::remember('whatsapp_ai_knowledge', 600, function() {
            $knowledge = "🏢 INFORMASI LAYANAN UTAMA & ESTIMASI WAKTU:\n";
            $masters = \App\Models\MasterLayanan::where('is_active', true)->orderBy('urutan')->get();
            foreach ($masters as $master) {
                $serviceUrl = $this->getPublicUrl() . "/layanan/" . $master->slug;
                $knowledge .= "SERVICE: " . strtoupper($master->nama_layanan) . " | ESTIMASI: " . ($master->estimasi_waktu ?: '3 Hari') . " | SYARAT: " . ($master->deskripsi_syarat ?: '-') . " | URL: {$serviceUrl}\n";
            }

            $knowledge .= "\nSERVICE NODES (SUB-LAYANAN):\n";
            $nodes = ServiceNode::where('is_active', true)->get();
            foreach ($nodes as $node) {
                $masterSlug = $node->masterLayanan->slug ?? 'umum';
                $serviceUrl = $this->getPublicUrl() . "/layanan/" . $masterSlug;
                $knowledge .= "NODE: " . strtoupper($node->name) . " | URL: {$serviceUrl}\n";
            }
            $faqs = PelayananFaq::all();
            if ($faqs->count() > 0) {
                $knowledge .= "\nPERTANYAAN UMUM (FAQ):\n";
                foreach ($faqs as $faq) {
                    $knowledge .= "Tanya: {$faq->question}\nJawab: {$faq->answer}\n";
                }
            }

            $knowledge .= "\nINFORMASI TERKINI & BERITA WILAYAH:\n";
            $news = Berita::published()->orderBy('published_at', 'desc')->take(3)->get();
            if ($news->count() > 0) {
                foreach ($news as $n) {
                    $date = $n->published_at->format('d M Y');
                    $knowledge .= "- [{$date}] {$n->judul}: {$n->ringkasan}\n";
                }
            }

            $desas = Desa::all();
            $totalPenduduk = $desas->sum('jumlah_penduduk');
            $totalLaki = $desas->sum('jumlah_laki_laki');
            $totalPerempuan = $desas->sum('jumlah_perempuan');
            $totalKk = $desas->sum('jumlah_kk');
            $villageNames = $desas->pluck('nama_desa')->join(', ');

            // --- AGGREGATE JSON STATS ---
            $eduStats = []; $jobStats = []; $religionStats = []; $totalStunting = 0; $desil1 = 0;

            foreach ($desas as $desa) {
                // Education
                $edu = $desa->stat_pendidikan;
                if (is_array($edu)) {
                    foreach ($edu as $item) {
                        $label = $item['nama'] ?? 'Lainnya';
                        $eduStats[$label] = ($eduStats[$label] ?? 0) + ($item['jumlah'] ?? 0);
                    }
                }
                // Job
                $job = $desa->stat_pekerjaan;
                if (is_array($job)) {
                    foreach ($job as $item) {
                        $label = $item['nama'] ?? 'Lainnya';
                        $jobStats[$label] = ($jobStats[$label] ?? 0) + ($item['jumlah'] ?? 0);
                    }
                }
                // Religion
                $rel = $desa->stat_agama;
                if (is_array($rel)) {
                    foreach ($rel as $item) {
                        $label = $item['nama'] ?? 'Lainnya';
                        $religionStats[$label] = ($religionStats[$label] ?? 0) + ($item['jumlah'] ?? 0);
                    }
                }
                // Health (Stunting)
                $health = $desa->stat_kesehatan;
                if (is_array($health)) {
                    $totalStunting += $health['totalStunting'] ?? 0;
                }
                
                // Welfare (DTSEN / Desil)
                $desil = $desa->stat_desil;
                if (is_array($desil)) {
                    $desil1 += $desil['totalDesil1'] ?? 0;
                }
            }

            $knowledge .= "\nSTATISTIK WILAYAH LENGKAP:\n";
            $knowledge .= "- Daftar 17 Desa: {$villageNames}\n";
            $knowledge .= "- Total Penduduk: {$totalPenduduk} jiwa (Laki-laki: {$totalLaki}, Perempuan: {$totalPerempuan})\n";
            $knowledge .= "- Total Keluarga (KK): {$totalKk}\n";
            
            $knowledge .= "\nPENDIDIKAN:\n";
            foreach (array_slice($eduStats, 0, 8) as $label => $count) $knowledge .= "- {$label}: {$count} orang\n";

            $knowledge .= "\nPEKERJAAN UTAMA:\n";
            foreach (array_slice($jobStats, 0, 8) as $label => $count) $knowledge .= "- {$label}: {$count} orang\n";

            $knowledge .= "\nAGAMA:\n";
            foreach ($religionStats as $label => $count) $knowledge .= "- {$label}: {$count} orang\n";

            $knowledge .= "\nKESEHATAN & SOSIAL (DTSEN):\n";
            $knowledge .= "- Total Kasus Stunting: {$totalStunting} balita\n";
            $knowledge .= "- Penduduk Kesejahteraan Rendah (Desil 1): {$desil1} jiwa\n";
            
            $serviceStats = [
                'total_layanan' => PublicService::count(),
                'total_umkm' => Umkm::count(),
            ];
            $knowledge .= "\nDATA LAYANAN DIGITAL:\n";
            $knowledge .= "- Permohonan Berkas Selesai: {$serviceStats['total_layanan']}\n";
            $knowledge .= "- Total UMKM Terdaftar: {$serviceStats['total_umkm']}\n";

            $knowledge .= "\nINFORMASI CUACA & RADAR (BMKG):\n";
            $weather = $this->weatherService->getForecast();
            if ($weather['success']) {
                $knowledge .= $weather['raw_text'] . "\n";
                $knowledge .= "PANTAUAN RADAR: " . $this->weatherService->getRadarAlert() . "\n";
            } else {
                $knowledge .= "Layanan cuaca BMKG sedang tidak tersedia.\n";
            }

            $knowledge .= "\n🚨 PERINGATAN KERAS UNTUK ANDA (AI):\n";
            $knowledge .= "- JANGAN PERNAH menyebutkan estimasi '14 hari' atau '14 hari kerja' untuk layanan KTP atau layanan apapun di sini.\n";
            $knowledge .= "- Gunakan HANYA data estimasi yang tertulis di DATA RESMI di atas.\n";
            $knowledge .= "- Jika di DATA RESMI tertulis '3 Hari Kerja', maka Anda WAJIB menyebutkan '3 Hari Kerja'.\n";

            return $knowledge;
        // });
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
