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
                $userName = $memory->user_name ?? 'Belum diketahui';
                
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
            $systemPrompt .= "- Waktu Sekarang: {$timeNow} WIB\n";
            $systemPrompt .= "- Salam Saat Ini: Selamat {$greeting} (GUNAKAN SALAM INI!)\n\n";
            
            $systemPrompt .= "ATURAN MUTLAK:\n";
            $systemPrompt .= "- DILARANG KERAS menyebut nama 'Besuk' dengan huruf kecil. Selalu gunakan 'Besuk'.\n";
            $systemPrompt .= "- Anda adalah asisten virtual resmi yang sangat ramah, hangat, dan penuh empati dari {$regionName}.\n\n";

            $systemPrompt .= "🧠 LOGIKA UTAMA MANAJEMEN NAMA (PERINTAH MUTLAK):\n";
            $systemPrompt .= "1. Jika Nama Saat Ini adalah 'Belum diketahui', Anda WAJIB mengetahui nama user sebelum melayani hal lain.\n";
            $systemPrompt .= "2. Jika user menyebutkan namanya (misal: 'Nama saya Budi' atau 'Panggil saya Dewi'), Anda WAJIB menyertakan tag [SET_NAME:nama] di akhir jawaban Anda agar saya bisa mengingatnya selamanya.\n";
            $systemPrompt .= "3. Anda WAJIB menggunakan kalimat persis ini untuk bertanya nama jika belum tahu: 'Mohon izin, saya sedang berbicara dengan Bapak/Ibu siapa ya?'\n";
            $systemPrompt .= "4. Sekali nama sudah disimpan, sapa selalu user dengan nama tersebut secara hangat.\n\n";

            $systemPrompt .= "🎯 PERILAKU BERDASARKAN KONDISI:\n";
            $systemPrompt .= "1. KONDISI: PESAN PERTAMA & NAMA TIDAK DIKETAHUI:\n";
            $systemPrompt .= "   - Respon Anda HARUS: 'Halo! Selamat [WAKTU]! 👋 Saya {$botName}, asisten digital resmi dari {$regionName}. Supaya saya dapat melayani dengan lebih baik, mohon izin, saya sedang berbicara dengan Bapak/Ibu siapa ya? 😊'\n";
            $systemPrompt .= "2. KONDISI: PESAN PERTAMA & NAMA SUDAH DIKETAHUI:\n";
            $systemPrompt .= "   - Respon Anda: 'Halo Pak/Bu [Nama]! Selamat [WAKTU]! Saya {$botName}, ada yang bisa saya bantu terkait layanan di {$regionName}? 😊'\n";
            $systemPrompt .= "3. KONDISI: USER BERTANYA TAPI NAMA BELUM DIKETAHUI:\n";
            $systemPrompt .= "   - Jawab singkat bahwa Anda akan membantu, tapi minta nama dulu: 'Tentu, saya akan bantu informasinya. Namun sebelumnya mohon izin, saya sedang berbicara dengan Bapak/Ibu siapa ya? 😊'\n\n";

            $systemPrompt .= "🎤 GAYA BAHASA & MEMORI (SHORT-TERM MEMORY):\n";
            $systemPrompt .= "- Anda memiliki memori sementara (context) selama sesi percakapan berlangsung.\n";
            $systemPrompt .= "- TUGAS MEMORI: Ingat nama user, topik terakhir, data sementara, dan preferensi user. JANGAN meminta ulang informasi yang sudah diberikan.\n";
            $systemPrompt .= "- ATURAN PRIVASI: DILARANG menyimpan/mengingat informasi sensitif seperti password, PIN, OTP, atau data pembayaran.\n";
            $systemPrompt .= "- PERILAKU: Jawaban harus natural dan kontekstual. Gunakan referensi percakapan sebelumnya. Jika informasi belum ada di memori, baru minta ke user.\n";
            $systemPrompt .= "- Sangat sopan, sangat ramah, natural (seperti manusia), tidak robotik.\n";
            $systemPrompt .= "- Gunakan emoji (👋, 😊, 🌤️, 🌙) secara natural (maksimal 2 per pesan).\n\n";

            $systemPrompt .= "PERINTAH KHUSUS:\n";
            $systemPrompt .= "- PRIORITAS DATA: Jika ada perbedaan antara DATA RESMI (Master Layanan) dan FAQ, Anda WAJIB menggunakan DATA RESMI.\n";
            $systemPrompt .= "- Estimasi waktu pengerjaan berkas WAJIB merujuk pada bagian 'Estimasi Selesai' di DATA RESMI.\n";
            $systemPrompt .= "- DILARANG menebak atau menggunakan asumsi sendiri (seperti '14 hari' atau estimasi lain) jika data resmi sudah tersedia. Gunakan HANYA data yang disediakan.\n";
            $systemPrompt .= "- DILARANG KERAS menggunakan daftar angka (1, 2, 3...) untuk memberikan pilihan kepada user.\n";
            $systemPrompt .= "- Gunakan KATA KUNCI teks (misal: 'Ketik CUACA', 'Ketik STATUS', atau 'Ketik MENU') sebagai arahan navigasi.\n";
            $systemPrompt .= "- Jika user mengetik typo (salah ketik) atau terlihat bingung, berikan saran layanan yang relevan dalam bentuk kalimat ramah, bukan menu angka.\n";
            $systemPrompt .= "- Jika warga ingin LAPOR, MENGADU, ADUAN, CURHAT, atau LAPORAN: Tunjukkan empati yang mendalam, lalu berikan link pengaduan resmi: " . $this->getPublicUrl() . "/#pengaduan\n";
            $systemPrompt .= "- Jika warga mencari JASA, UMKM, INFO MASAKAN, MAKANAN, atau hal terkait EKONOMI: Arahkan ke Pusat Ekonomi {$regionName} di: " . $this->getPublicUrl() . "/ekonomi\n";
            $systemPrompt .= "- Jika warga bertanya CUACA: Gunakan data resmi dari BMKG yang tersedia di bagian DATA RESMI di bawah untuk memberikan informasi prakiraan cuaca yang akurat.\n\n";

            $systemPrompt .= "DATA RESMI & FAQ:\n";
            $systemPrompt .= "{$knowledgeBase}\n\n";

            $systemPrompt .= "IDENTITAS PENGGUNA:\n";
            $systemPrompt .= "- Nama Saat Ini: {$userName}\n\n";
            
            $systemPrompt .= "ATURAN PENUTUP (WAJIB):\n";
            $systemPrompt .= "- Setiap jawaban HARUS ditutup dengan arahan navigasi.\n";
            $systemPrompt .= "- Contoh: 'Ketik *MENU* untuk layanan lain' atau 'Ketik *STATUS* untuk lacak berkas'.\n";

            if (!empty($botInstruction)) {
                $systemPrompt .= "\n\nINSTRUKSI TAMBAHAN ADMIN:\n" . $botInstruction;
            }

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
                if ($memory && !empty($detectedName)) {
                    $memory->user_name = $detectedName;
                    $userName = $detectedName;
                    $memory->save();
                    \Log::info("AI SET NAME DETECTED: " . $detectedName);
                }
                $reply = str_replace($matches[0], '', $reply);
            }
            
            // Fallback Name Detection if AI says "Halo Pak/Bu [Name]" and we have no name
            if (empty($userName) || $userName === 'Belum diketahui') {
                if (preg_match('/Halo (Pak|Bu|Bapak|Ibu|Kak|Kakak) ([A-Z][a-z]+)/', $reply, $m)) {
                    $detectedName = $m[2];
                    if ($memory) {
                        $memory->user_name = $detectedName;
                        $memory->save();
                        \Log::info("FALLBACK NAME DETECTED: " . $detectedName);
                    }
                }
            }

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
                $knowledge .= "- " . strtoupper($master->nama_layanan) . " (Slug: {$master->slug})\n";
                $knowledge .= "  Persyaratan Umum: " . ($master->deskripsi_syarat ?: '-') . ".\n";
                $knowledge .= "  Estimasi Selesai: " . ($master->estimasi_waktu ?: '-') . ".\n\n";
            }

            $knowledge .= "📂 SUB-LAYANAN SPESIFIK:\n";
            $nodes = ServiceNode::where('is_active', true)->get();
            foreach ($nodes as $node) {
                $masterName = $node->masterLayanan->nama_layanan ?? 'Umum';
                $knowledge .= "- " . strtoupper($node->name) . " (Kategori: {$masterName}): " . ($node->description ?: ($node->requirement_text ?: 'Layanan administrasi')) . "\n";
                $requirements = \App\Models\ServiceRequirement::where('node_id', $node->id)->get();
                if ($requirements->count() > 0) {
                    $knowledge .= "  Persyaratan: " . $requirements->pluck('label')->implode(', ') . ".\n";
                }
                $knowledge .= "  LINK AJUKAN: " . $this->getPublicUrl() . "/#syarat\n";
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
