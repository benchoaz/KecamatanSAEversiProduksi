<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsappSession;

class IntentHandler
{
    protected StatusHandler $statusHandler;
    protected SyaratHandler $syaratHandler;
    protected UmkmHandler $umkmHandler;
    protected JasaHandler $jasaHandler;

    protected ComplaintHandler $complaintHandler;
    protected OwnerHandler $ownerHandler;
    protected \App\Services\FaqSearchService $faqSearchService;
    protected AiHandler $aiHandler;

    public function __construct(
        StatusHandler $statusHandler,
        SyaratHandler $syaratHandler,
        UmkmHandler $umkmHandler,
        JasaHandler $jasaHandler,
        ComplaintHandler $complaintHandler,
        OwnerHandler $ownerHandler,
        \App\Services\FaqSearchService $faqSearchService,
        AiHandler $aiHandler
    ) {
        $this->statusHandler = $statusHandler;
        $this->syaratHandler = $syaratHandler;
        $this->umkmHandler = $umkmHandler;
        $this->jasaHandler = $jasaHandler;
        $this->complaintHandler = $complaintHandler;
        $this->ownerHandler = $ownerHandler;
        $this->faqSearchService = $faqSearchService;
        $this->aiHandler = $aiHandler;
    }

    /**
     * Handle incoming message and detect intent
     */
    public function handle(string $phone, string $message): array
    {
        $messageLower = strtolower(trim($message));
        $session = WhatsappSession::where('phone', $phone)->first();
        $state = $session ? $session->state : null;

        // 1. GLOBAL KEYWORDS: Reset to main menu (HIGHEST PRIORITY)
        if ($this->matchesIntent($messageLower, ['menu', 'help', 'bantuan', '0', 'batal', 'stop', 'berhenti', 'cancel'])) {
            // If it's a cancel keyword, clear session first
            if (in_array($messageLower, ['batal', 'stop', 'berhenti', 'cancel'])) {
                if ($session) $session->clear();
                return [
                    'success' => true,
                    'intent' => 'cancel',
                    'reply' => "👋 *Siaaap!* Permintaan Anda telah dibatalkan.\n\nKetik *MENU* untuk layanan lainnya. 😊",
                    'state_update' => null
                ];
            }
            return $this->getMainMenu();
        }

        // 2. GREETINGS: Warm response for hello/hi
        $greetings = ['halo', 'hai', 'hallo', 'pagi', 'siang', 'sore', 'malam', 'assalamualaikum', 'salam', 'tes', 'test', 'oi'];
        if ($this->matchesIntent($messageLower, $greetings)) {
            // Give it to AI for a warm personalized greeting if AI is active
            $aiResponse = $this->aiHandler->handle($phone, $message);
            if ($aiResponse) return $aiResponse;

            // Manual warm fallback if AI is off
            return [
                'success' => true,
                'intent' => 'greeting',
                'reply' => "👋 *Halo! Selamat datang di Layanan Digital " . $this->getRegionName() . "*.\n\n" .
                    "Ada yang bisa saya bantu hari ini? Silakan ketik pertanyaan Anda atau ketik *MENU* untuk melihat daftar layanan kami. 😊",
                'state_update' => null,
            ];
        }

        // 3. EMERGENCY DETECTOR: Handle emergency keywords (CONTAIN matching for safety)
        $emergencyKeywords = [
            'kebakaran', 'api', 'bom',
            'ambulance', 'ambulans', 'kecelakaan', 'darurat', 'pingsan', 'sekarat', 'kritis',
            'polisi', 'maling', 'rampok', 'begal', 'bunuh', 'kriminal',
            'korupsi', 'pungli', 'penyelewengan',
            'hamil', 'melahirkan', 'persalinan', 'kontraksi'
        ];
        if ($this->containsIntent($messageLower, $emergencyKeywords)) {
            return $this->handleEmergencyResponse($messageLower);
        }

        // 4. STATE NAVIGATION: Handle Nested Menus
        if ($state && str_starts_with($state, 'NAV_PATH:')) {
            $path = str_replace('NAV_PATH:', '', $state);
            return $this->handleMenuNavigation($phone, $messageLower, $path);
        }

        // 5. ROOT MENU NAVIGATION
        $activeMenu = $this->getActiveMenuMapping();
        foreach ($activeMenu as $number => $item) {
            if ($this->isSelection($messageLower, (string)$number)) {
                // If this is a submenu, enter it
                if (($item['action'] ?? '') === 'submenu') {
                    return $this->enterSubmenu($phone, (string)($number - 1), $item);
                }
                // Otherwise execute the direct action
                return $this->executeMenuAction($item['action'] ?? 'custom', $phone, $item);
            }
        }

        // --- KEYWORD FALLBACKS ---

        // Direct PIN check (6 digits)
        if (preg_match('/^[0-9]{6}$/', $messageLower)) {
            return $this->statusHandler->handle($phone, $messageLower);
        }

        // Status check intent
        if ($this->matchesIntent($messageLower, ['status', 'cek', 'lacak'])) {
            $query = trim(str_replace(['status', 'cek', 'lacak'], '', $messageLower));
            return $this->statusHandler->handle($phone, $query ?: null);
        }

        // LUPA PIN (Forgot PIN) intent
        if ($this->matchesIntent($messageLower, ['lupa pin', 'lupin', 'forgot pin', 'lupa', 'forget'])) {
            return $this->statusHandler->handleForgotPin($phone);
        }

        // SYARAT (requirements) intent - with link when no specific query
        if (
            str_starts_with($messageLower, 'syarat') ||
            str_starts_with($messageLower, 'buat') ||
            $this->matchesIntent($messageLower, ['persyaratan', 'ketentuan'])
        ) {
            // Extract query - remove "syarat" or "buat" prefix
            $query = str_replace(['syarat', 'persyaratan', 'ketentuan', 'buat'], '', $messageLower);
            $query = trim($query);

            // If just "syarat" or "buat" without query, show link to layanan page
            if (empty($query) || strlen($query) < 2) {
                return $this->getLayananLink();
            }
            return $this->syaratHandler->search($query);
        }

        // UMK search intent - with link when no query
        if ($this->matchesIntent($messageLower, ['umkm', 'umk', 'produk', 'etalase'])) {
            // Extract query from message (remove keyword)
            $query = str_replace(['umkm', 'umk', 'produk', 'etalase'], '', $messageLower);
            $query = trim($query);
            // If just keyword without query, show link
            if (empty($query) || strlen($query) < 2) {
                return $this->getUmkmLink();
            }
            return $this->umkmHandler->search($query);
        }

        // JASA search intent
        if (str_starts_with($messageLower, 'jasa')) {
            $query = trim(substr($message, 4));
            return $this->jasaHandler->search($query);
        }

        // LOKER keyword
        if ($this->matchesIntent($messageLower, ['loker', 'lowongan', 'kerja'])) {
            $baseUrl = $this->getPublicUrl();
            return [
                'success' => true,
                'intent' => 'jasa_link',
                'reply' => "🔧 *Direktori Jasa & Tenaga AhlI*\n\n" .
                    "Temukan tukang, tenaga harian, dan penyedia jasa lokal:\n" .
                    "{$baseUrl}/ekonomi?tab=jasa\n\n" .
                    "Ketik *MENU* untuk kembali.",
                'state_update' => null,
            ];
        }

        // Complaint submission intent
        if ($this->matchesIntent($messageLower, ['pengaduan', 'lapor', 'aduan', 'complaint'])) {
            return $this->complaintHandler->initiate($phone);
        }

        // --- STATE BASED HANDLING ---
        if ($session && $session->state === 'ADM_SUBMENU') {
            if ($this->isSelection($messageLower, '1') || $messageLower === 'status' || $messageLower === 'cek status') {
                return $this->statusHandler->handle($phone, 'STATUS');
            }
            if ($this->isSelection($messageLower, '2') || $messageLower === 'syarat') {
                return $this->getLayananLink();
            }
            if ($this->isSelection($messageLower, '3') || $messageLower === 'menu' || $messageLower === 'kembali') {
                return $this->getMainMenu();
            }
            
            // AI Fallback inside submenu
            $aiResponse = $this->aiHandler->handle($phone, $message);
            if ($aiResponse) return $aiResponse;

            // If user types something else and AI fails, show submenu again
            return $this->getAdministrasiSubmenu();
        }

        if ($session && $session->state === 'MENU_ADMIN') {
            if ($this->isSelection($messageLower, '1')) {
                return $this->syaratHandler->search(null);
            }
            if ($this->isSelection($messageLower, '2')) {
                return $this->statusHandler->handle($phone, null);
            }
            if ($this->isSelection($messageLower, '3')) {
                return $this->complaintHandler->initiate($phone, 'pelayanan');
            }
        }

        // Owner toggle intent
        if ($this->matchesIntent($messageLower, ['toggle', 'aktif', 'nonaktif', 'on', 'off', 'kelola'])) {
            return $this->ownerHandler->initiate($phone);
        }

        // Quick Holiday Toggles
        if ($messageLower === 'libur' || $messageLower === 'buka') {
            return $this->ownerHandler->toggleHolidayStatus($phone, $messageLower);
        }

        // --- FAQ NATURAL LANGUAGE FALLBACK ---
        $faqData = $this->faqSearchService->search($messageLower);
        if ($faqData['found']) {
            if (isset($faqData['multiple']) && $faqData['multiple']) {
                $reply = "Ditemukan beberapa topik yang mungkin relevan:\n\n";
                foreach ($faqData['results'] as $i => $res) {
                    $num = $i + 1;
                    $reply .= "{$num}. SYARAT " . strtoupper($res['question']) . "\n";
                }
                $reply .= "\nKetik kata kunci yang lebih spesifik atau pilih dari menu.";
                return [
                    'success' => true,
                    'intent' => 'faq_suggestions',
                    'reply' => $reply,
                    'state_update' => null,
                ];
            }

            $top = $faqData['results'][0];
            return [
                'success' => true,
                'intent' => 'faq_match',
                'reply' => "✅ *{$top['question']}*\n\n{$top['answer']}\n\nKetik *MENU* untuk kembali.",
                'state_update' => null,
            ];
        }

        // --- AI SMART FALLBACK ---
        $aiResponse = $this->aiHandler->handle($phone, $message);
        if ($aiResponse !== null) {
            return $aiResponse;
        }

        // Unknown intent (Jika AI mati atau error)
        return [
            'success' => true,
            'intent' => 'unknown',
            'reply' => $this->getUnknownIntentMessage(),
            'state_update' => null,
        ];
    }

    protected function matchesIntent(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if ($message === $keyword || str_starts_with($message, $keyword . ' ')) {
                return true;
            }
        }
        return false;
    }

    protected function containsIntent(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }

    protected function getActiveMenuMapping(array $menuItems = null): array
    {
        if ($menuItems === null) {
            $profile = appProfile();
            $menuItems = $profile->whatsapp_bot_menu;
            if (is_string($menuItems)) {
                $menuItems = json_decode($menuItems, true);
            }
            $menuItems = $menuItems ?: $this->defaultBotMenu();
        }

        $mapping = [];
        $numbering = 1;
        foreach ($menuItems as $item) {
            if (!($item['enabled'] ?? true)) continue;
            $mapping[$numbering] = $item;
            $numbering++;
        }
        return $mapping;
    }

    protected function handleMenuNavigation(string $phone, string $message, string $path): array
    {
        $profile = appProfile();
        $fullMenu = $profile->whatsapp_bot_menu;
        if (is_string($fullMenu)) $fullMenu = json_decode($fullMenu, true);
        $fullMenu = $fullMenu ?: $this->defaultBotMenu();

        $indices = explode('.', $path);
        $currentMenu = $fullMenu;
        $parentLabel = 'MENU UTAMA';

        foreach ($indices as $idx) {
            if (isset($currentMenu[$idx]['children'])) {
                $parentLabel = $currentMenu[$idx]['label'];
                $currentMenu = $currentMenu[$idx]['children'];
            } else {
                return $this->getMainMenu();
            }
        }

        $activeMapping = $this->getActiveMenuMapping($currentMenu);
        foreach ($activeMapping as $number => $item) {
            if ($this->isSelection($message, (string)$number)) {
                $action = $item['action'] ?? 'custom';
                if ($action === 'submenu' && !empty($item['children'])) {
                    return $this->enterSubmenu($phone, $path . '.' . ($number - 1), $item);
                }
                if ($action === 'back') {
                    $newPath = count($indices) > 1 ? implode('.', array_slice($indices, 0, -1)) : null;
                    if ($newPath === null) return $this->getMainMenu();
                    return $this->handleMenuNavigation($phone, 'RE-RENDER', $newPath);
                }
                return $this->executeMenuAction($action, $phone, $item);
            }
        }

        return [
            'success' => true,
            'intent' => 'submenu_render',
            'reply' => $this->renderMenu($activeMapping, $parentLabel),
            'state_update' => 'NAV_PATH:' . $path,
        ];
    }

    protected function enterSubmenu(string $phone, string $newPath, array $item): array
    {
        $children = $item['children'] ?? [];
        $activeMapping = $this->getActiveMenuMapping($children);

        return [
            'success' => true,
            'intent' => 'submenu_enter',
            'reply' => $this->renderMenu($activeMapping, $item['label'] ?? 'Sub-Menu'),
            'state_update' => 'NAV_PATH:' . $newPath,
        ];
    }

    protected function executeMenuAction(string $action, string $phone, array $item = []): array
    {
        switch ($action) {
            case 'administrasi':
                return $this->getAdministrasiSubmenu();
            case 'umkm_produk':
                $baseUrl = $this->getPublicUrl();
                return [
                    'success' => true,
                    'intent' => 'umkm_produk',
                    'reply' => "🛍️ *PRODUK UMKM LOKAL*\n\nTemukan produk olahan dan kerajinan tangan karya warga lokal:\n\n👉 {$baseUrl}/ekonomi?tab=produk\n\nKetik *MENU* untuk kembali.",
                    'state_update' => null,
                ];
            case 'jasa':
                $baseUrl = $this->getPublicUrl();
                return [
                    'success' => true,
                    'intent' => 'jasa',
                    'reply' => "🔧 *CARI JASA & TENAGA AHLI*\n\nTemukan tukang, ART, ojek, dan tenaga harian di sekitar Anda:\n\n👉 {$baseUrl}/ekonomi?tab=jasa\n\nAtau ketik jenis jasa yang Anda cari:\nContoh: *jasa tukang*, *jasa ojek*\n\nKetik *MENU* untuk kembali.",
                    'state_update' => 'MENU_JASA',
                ];
            case 'pengaduan':
                return $this->complaintHandler->initiate($phone, 'pengaduan');
            case 'kelola_profil':
                return $this->ownerHandler->initiate($phone);
            case 'custom':
                if (!empty($item['url'])) {
                    return [
                        'success' => true,
                        'intent' => 'external_link',
                        'reply' => "👉 *{$item['label']}*\n\nSilakan akses tautan berikut:\n{$item['url']}\n\nKetik *MENU* untuk kembali.",
                        'state_update' => null,
                    ];
                }
                return $this->getMainMenu();
            default:
                return $this->getMainMenu();
        }
    }

    protected function getMainMenu(): array
    {
        $regionName = strtoupper(appProfile()->region_name ?? 'BESUK');
        $activeMapping = $this->getActiveMenuMapping();

        return [
            'success'      => true,
            'intent'       => 'menu',
            'reply'        => $this->renderMenu($activeMapping, "KECAMATAN {$regionName}"),
            'state_update' => null,
        ];
    }

    protected function renderMenu(array $mapping, string $title): string
    {
        $menu  = "🏛️ *LAYANAN DIGITAL " . strtoupper($title) . "*\n\n";
        $menu .= "Silakan pilih layanan (Ketik angka):\n\n";

        foreach ($mapping as $num => $item) {
            $label = $item['label'] ?? 'Pilihan';
            $desc  = $item['description'] ?? '';
            $menu .= "{$num}. *{$label}*" . ($desc ? "\n   _{$desc}_" : "") . "\n\n";
        }

        $menu .= "Atau ketik langsung apa yang ingin Anda tanyakan. 😊\n\n";
        $menu .= "Ketik *MENU* untuk kembali.";
        return $menu;
    }

    protected function getPublicUrl(): string
    {
        $profile = appProfile();
        if (!empty($profile->public_url)) {
            return rtrim($profile->public_url, '/');
        }
        return rtrim(env('PUBLIC_BASE_URL', config('app.url', 'https://kecamatanbesuk.my.id')), '/');
    }

    protected function defaultBotMenu(): array
    {
        return [
            ['number' => '1', 'label' => 'LAYANAN DAN BERKAS',    'description' => 'Cek Syarat dan Lacak Berkas Anda',    'action' => 'administrasi',   'enabled' => true],
            ['number' => '2', 'label' => 'BELANJA PRODUK LOKAL',  'description' => 'Etalase UMKM dan Produk Unggulan',    'action' => 'umkm_produk',    'enabled' => true],
            ['number' => '3', 'label' => 'JASA DAN TENAGA AHLI',  'description' => 'Cari Tukang, Ojek, dan Tenaga Harian','action' => 'jasa',           'enabled' => true],
            ['number' => '4', 'label' => 'PENGADUAN DAN ASPIRASI','description' => 'Sampaikan Laporan atau Saran Anda',   'action' => 'pengaduan',      'enabled' => true],
            ['number' => '5', 'label' => 'DAFTARKAN TOKO ATAU JASA','description' => 'Kelola Profil Usaha dan Jasa Anda', 'action' => 'kelola_profil',  'enabled' => true],
        ];
    }

    protected function isSelection(string $message, string $number): bool
    {
        $message = trim($message);
        if ($message === $number) return true;
        
        $emojis = ['1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5'];
        return isset($emojis[$number]) && isset($emojis[$message]) && $emojis[$message] === $emojis[$number];
    }

    protected function getUnknownIntentMessage(): string
    {
        return "🙏 *Mohon maaf*, saya belum mengenali pesan tersebut.\n\n" .
            "Agar dapat kami layani dengan baik, silakan pilih nomor layanan (1-5) atau ketik *MENU* untuk melihat daftar layanan utama kami.\n\n" .
            "Terima kasih atas pengertiannya! 😊";
    }

    protected function getUmkmLink(): array
    {
        $baseUrl = $this->getPublicUrl();
        $umkmUrl = $baseUrl . '/ekonomi?tab=produk';

        return [
            'success' => true,
            'intent' => 'umkm_link',
            'reply' => "🛍️ *ETALASE PRODUK UMKM*\n\nLihat semua produk pilihan warga {$this->getRegionName()} di:\n\n👉 {$umkmUrl}\n\nKetik *MENU* untuk kembali.",
            'state_update' => null,
        ];
    }

    public function getLayananLink(): array
    {
        $baseUrl    = $this->getPublicUrl();
        $layananUrl = $baseUrl . '/#layanan';

        return [
            'success' => true,
            'intent'  => 'syarat_link',
            'reply'   => "🏛️ *LAYANAN ADMINISTRASI*\n\nAjukan Secara Online:\n👉 {$layananUrl}\n\nAtau ketik syarat yang Anda butuhkan (contoh: *syarat ktp*).\n\nKetik *MENU* untuk kembali.",
            'state_update' => 'ADM_SUBMENU',
        ];
    }

    protected function getRegionName(): string
    {
        $profile = appProfile();
        return $profile->region_name ?? 'Kecamatan';
    }

    public function getAdministrasiSubmenu(): array
    {
        $reply = "🏛️ *MENU ADMINISTRASI*\n\n";
        $reply .= "Silakan pilih layanan yang diinginkan:\n\n";
        $reply .= "1. *STATUS* - Lacak Berkas Anda\n";
        $reply .= "2. *SYARAT* - Syarat & Ajukan Online\n";
        $reply .= "3. *MENU* - Kembali ke Menu Utama\n\n";
        $reply .= "Ketik angka *1*, *2*, atau *3*.\n";
        $reply .= "Atau ketik *MENU* kapan saja.";

        return [
            'success' => true,
            'intent' => 'administrasi_submenu',
            'reply' => $reply,
            'state_update' => 'ADM_SUBMENU',
        ];
    }

    protected function handleEmergencyResponse(string $message): array
    {
        $reply = "🚨 *LAYANAN DARURAT CEPAT*\n\n";
        if (str_contains($message, 'kebakaran') || str_contains($message, 'api')) {
            $reply .= "🔥 *KEBAKARAN:* Segera hubungi Pemadam Kebakaran / BPBD di *112*\n\n";
        } elseif (str_contains($message, 'korupsi') || str_contains($message, 'pungli') || str_contains($message, 'penyelewengan')) {
            $reply .= "⚖️ *ADUAN KORUPSI/PUNGLI:* Silakan lapor melalui SP4N LAPOR:\n👉 https://www.lapor.go.id\n\n";
        } elseif ($this->containsIntent($message, ['polisi', 'maling', 'rampok', 'begal', 'kriminal'])) {
            $reply .= "👮 *KEAMANAN (Polisi):* Hubungi Call Center Polri di *110*\n\n";
        } elseif ($this->containsIntent($message, ['ambulance', 'ambulans', 'kecelakaan', 'hamil', 'melahirkan', 'pingsan'])) {
            $reply .= "🚑 *DARURAT MEDIS / AMBULANCE:* Segera hubungi Call Center Kesehatan di *119*\n\n";
            if (str_contains($message, 'hamil') || str_contains($message, 'melahirkan')) {
                $reply .= "🤰 *INFO PERSALINAN:* Tetap tenang, siapkan buku KIA/KK, dan segera menuju Puskesmas/RS terdekat atau hubungi bidan desa.\n\n";
            }
        }

        $reply .= "🆘 *CALL CENTER KAB. PROBOLINGGO:*\n";
        $reply .= "☎️ Telp: (0298) 343 0000\n";
        $reply .= "🟢 WA: 081 8181 91 119 *(Khusus Ambulans)*\n\n";
        $reply .= "Ketik *MENU* untuk layanan lainnya.";

        return [
            'success' => true,
            'intent' => 'emergency',
            'reply' => $reply,
            'state_update' => null,
        ];
    }
}
