<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsappSession;

class IntentHandler
{
    protected StatusHandler $statusHandler;
    protected SyaratHandler $syaratHandler;
    protected UmkmHandler $umkmHandler;
    protected JasaHandler $jasaHandler;
    protected LokerHandler $lokerHandler;
    protected ComplaintHandler $complaintHandler;
    protected OwnerHandler $ownerHandler;

    public function __construct(
        StatusHandler $statusHandler,
        SyaratHandler $syaratHandler,
        UmkmHandler $umkmHandler,
        JasaHandler $jasaHandler,
        LokerHandler $lokerHandler,
        ComplaintHandler $complaintHandler,
        OwnerHandler $ownerHandler
    ) {
        $this->statusHandler = $statusHandler;
        $this->syaratHandler = $syaratHandler;
        $this->umkmHandler = $umkmHandler;
        $this->jasaHandler = $jasaHandler;
        $this->lokerHandler = $lokerHandler;
        $this->complaintHandler = $complaintHandler;
        $this->ownerHandler = $ownerHandler;
    }

    /**
     * Handle incoming message and detect intent
     */
    public function handle(string $phone, string $message): array
    {
        $messageLower = strtolower(trim($message));

        // Menu intent
        if ($this->matchesIntent($messageLower, ['menu', 'help', 'bantuan'])) {
            return [
                'success' => true,
                'intent' => 'menu',
                'reply' => $this->getMainMenu()['reply'],
                'state_update' => null,
            ];
        }

        // --- NUMERIC SELECTION (Top Level) ---
        if ($this->isSelection($messageLower, '1')) {
            return $this->statusHandler->handle($phone, 'STATUS');
        }

        if ($this->isSelection($messageLower, '2')) {
            $baseUrl = config('app.public_base_url', config('app.url', 'https://babette-nonslanderous-randi.ngrok-free.dev'));
            return [
                'success' => true,
                'intent' => 'syarat_link',
                'reply' => "📋 *Informasi & Ajukan Layanan*\n\n" .
                    "Lihat daftar lengkap layanan dan syarat pengajuan:\n\n" .
                    "🔗 " . $baseUrl . "/layanan\n\n" .
                    "Ketik MENU untuk kembali.",
                'state_update' => null,
            ];
        }

        if ($this->isSelection($messageLower, '3')) {
            $baseUrl = config('app.public_base_url', config('app.url', 'https://babette-nonslanderous-randi.ngrok-free.dev'));
            return [
                'success' => true,
                'intent' => 'umkm_link',
                'reply' => "🛍️ *Lihat Etalase Produk UMK*\n\n" .
                    "Lihat produk-produk lokal dari UMK Besuk:\n\n" .
                    "🔗 " . $baseUrl . "/ekonomi\n\n" .
                    "Ketik MENU untuk kembali.",
                'state_update' => null,
            ];
        }

        if ($this->isSelection($messageLower, '4')) {
            $baseUrl = config('app.public_base_url', config('app.url', 'https://babette-nonslanderous-randi.ngrok-free.dev'));
            return [
                'success' => true,
                'intent' => 'loker_link',
                'reply' => "👷 *Info Lowongan Kerja*\n\n" .
                    "Lihat lowongan kerja terbaru:\n\n" .
                    "🔗 " . $baseUrl . "/ekonomi\n\n" .
                    "Ketik MENU untuk kembali.",
                'state_update' => null,
            ];
        }

        if ($this->isSelection($messageLower, '5')) {
            return $this->complaintHandler->initiate($phone, 'pengaduan');
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
        if (str_starts_with($messageLower, 'syarat') || $this->matchesIntent($messageLower, ['persyaratan', 'ketentuan'])) {
            $query = str_replace(['syarat', 'persyaratan', 'ketentuan'], '', $messageLower);
            $query = trim($query);
            // If just "syarat" without query, show link to layanan page
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

        // LOKER search intent - with link when no query
        if ($this->matchesIntent($messageLower, ['loker', 'lowongan', 'kerja'])) {
            $query = str_replace(['loker', 'lowongan', 'kerja'], '', $messageLower);
            $query = trim($query);
            // If just keyword without query, show link
            if (empty($query) || $messageLower === 'loker' || $messageLower === 'lowongan') {
                return $this->getLokerLink();
            }
            return $this->lokerHandler->search($query);
        }

        // Complaint submission intent
        if ($this->matchesIntent($messageLower, ['pengaduan', 'lapor', 'aduan', 'complaint'])) {
            return $this->complaintHandler->initiate($phone);
        }

        // --- STATE BASED HANDLING ---
        $session = WhatsappSession::where('phone', $phone)->first();
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

        // Unknown intent
        return [
            'success' => true,
            'intent' => 'unknown',
            'reply' => $this->getUnknownIntentMessage(),
            'state_update' => null,
        ];
    }

    /**
     * Check if message matches any of the intent keywords
     */
    protected function matchesIntent(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_starts_with($message, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Menu intent response
     */
    protected function getMainMenu(): array
    {
        $regionName = strtoupper(appProfile()->region_name ?? 'BESUK');
        $baseUrl = config('app.url', 'https://babette-nonslanderous-randi.ngrok-free.dev');

        $menu = "🏛️ *MENU LAYANAN KECAMATAN {$regionName}*\n\n";
        $menu .= "Silakan pilih layanan (Ketik angka):\n\n";
        $menu .= "1️⃣ *Administrasi* – Syarat & Status Berkas\n";
        $menu .= "2️⃣ *Loker & UMKM* – Kerja & Produk Desa\n";
        $menu .= "   └ Ketik: KELOLA (untuk Aktif/Nonaktifkan)\n\n";
        $menu .= "3️⃣ *Jasa* – Cari Tukang/Servis\n";
        $menu .= "   └ Ketik: KELOLA (untuk Aktif/Nonaktifkan)\n\n";
        $menu .= "4️⃣ *Pengaduan* – Aspirasi Warga\n\n";
        $menu .= "5️⃣ *Kelola Data* – Aktif/Nonaktifkan Data Anda\n\n";
        $menu .= "_Ketik MENU kapan saja untuk kembali._";

        return [
            'success' => true,
            'intent' => 'menu',
            'reply' => $menu,
            'state_update' => null,
        ];
    }

    /**
     * @deprecated Use getMainMenu() instead
     */
    protected function menuIntent(): array
    {
        return $this->getMainMenu();
    }

    /**
     * Map basic numeric strings to technical emojis often sent by WA
     */
    protected function isSelection(string $message, string $number): bool
    {
        $message = trim($message);

        // Pure numeric match
        if ($message === $number)
            return true;

        // Emoji match mapping
        $emojis = [
            '1' => '1️⃣',
            '2' => '2️⃣',
            '3' => '3️⃣',
            '4' => '4️⃣',
            '5' => '5️⃣',
        ];

        return isset($emojis[$number]) && $message === $emojis[$number];
    }

    /**
     * Unknown intent message
     */
    protected function getUnknownIntentMessage(): string
    {
        return "Maaf, saya tidak mengerti pesan Anda. Ketik *MENU* untuk melihat daftar layanan (1-5).";
    }

    /**
     * Get UMK/Produk link
     */
    protected function getUmkmLink(): array
    {
        $baseUrl = config('app.url');
        $umkmUrl = $baseUrl . '/ekonomi?tab=produk';

        return [
            'success' => true,
            'intent' => 'umkm_link',
            'reply' => "🛍️ *ETALASE PRODUK UMK*\n\n" .
                "Lihat semua produk UMK {$this->getRegionName()} di:\n\n" .
                "🔗 {$umkmUrl}\n\n" .
                "Anda juga bisa ketik nama produk yang dicari.\n" .
                "Contoh: *umkm bakso*\n\n" .
                "Ketik *MENU* untuk kembali.",
            'state_update' => null,
        ];
    }

    /**
     * Get Loker/Job listing link
     */
    protected function getLokerLink(): array
    {
        $baseUrl = config('app.url');
        $lokerUrl = $baseUrl . '/loker';
        $daftarUrl = $baseUrl . '/loker/pasang';

        return [
            'success' => true,
            'intent' => 'loker_link',
            'reply' => "👷 *LOWONGAN KERJA*\n\n" .
                "📋 Lihat info lowongan kerja:\n" .
                "🔗 {$lokerUrl}\n\n" .
                "📝 Pasang lowongan kerja:\n" .
                "🔗 {$daftarUrl}\n\n" .
                "Anda juga bisa ketik kata kunci.\n" .
                "Contoh: *loker tukang*\n\n" .
                "Ketik *MENU* untuk kembali.",
            'state_update' => null,
        ];
    }

    /**
     * Get Layanan/Syarat link
     */
    protected function getLayananLink(): array
    {
        $baseUrl = config('app.url');
        $layananUrl = $baseUrl . '/layanan';

        return [
            'success' => true,
            'intent' => 'syarat_link',
            'reply' => "📋 *LAYANAN KECAMATAN*\n\n" .
                "Lihat daftar layanan dan syarat pengajuan di:\n\n" .
                "🔗 {$layananUrl}\n\n" .
                "Anda juga bisa langsung tanya syarat layanan tertentu.\n" .
                "Contoh: *syarat ktp*, *syarat kk*\n\n" .
                "Ketik *MENU* untuk kembali.",
            'state_update' => null,
        ];
    }

    /**
     * Get region name for responses
     */
    protected function getRegionName(): string
    {
        $profile = appProfile();
        return $profile->region_name ?? 'Kecamatan';
    }
}
