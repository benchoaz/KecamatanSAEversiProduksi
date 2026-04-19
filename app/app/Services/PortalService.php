<?php

namespace App\Services;

use App\Models\Umkm;
use App\Models\WorkDirectory;
use App\Models\UmkmLocal;
use App\Models\WahaN8nSetting;
use App\Models\PortalLoginToken;
use App\Models\PublicService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PortalService
{
    /**
     * Normalize phone number to base format (removing leading 0 or 62)
     */
    public function normalizePhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        $base = $clean;
        
        if (str_starts_with($clean, '62')) {
            $base = substr($clean, 2);
        } elseif (str_starts_with($clean, '0')) {
            $base = substr($clean, 1);
        }
        
        return ltrim($base, '0');
    }

    /**
     * Standardize phone for WhatsApp sending (628...)
     */
    public function formatForWA(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            return '62' . substr($clean, 1);
        }
        if (!str_starts_with($clean, '62')) {
            return '62' . $clean;
        }
        return $clean;
    }

    /**
     * Find assets (UMKM, Jasa, UMKM Local) by phone number
     */
    public function findAssetsByPhone(string $phone): array
    {
        $basePhone = $this->normalizePhone($phone);
        $likeClause = '%' . $basePhone . '%';

        return [
            'umkm' => Umkm::where('no_wa', 'like', $likeClause)->first(),
            'jasa' => WorkDirectory::where('contact_phone', 'like', $likeClause)->first(),
            'umkmLocal' => UmkmLocal::where('contact_wa', 'like', $likeClause)->first(),
        ];
    }

    /**
     * Request and send a magic link access
     */
    public function requestAccess(string $inputPhone): bool
    {
        $assets = $this->findAssetsByPhone($inputPhone);
        $umkm = $assets['umkm'];
        $jasa = $assets['jasa'];
        $umkmLocal = $assets['umkmLocal'];

        if (!$umkm && !$jasa && !$umkmLocal) {
            Log::info('PortalService: No assets found for phone ' . $inputPhone);
            return false;
        }

        // Authoritative data
        $authPhone = $umkm ? $umkm->no_wa : ($jasa ? $jasa->contact_phone : $umkmLocal->contact_wa);
        $name = $umkm ? $umkm->nama_pemilik : ($jasa ? $jasa->display_name : $umkmLocal->name);
        
        $phoneSend = $this->formatForWA($authPhone);
        $expiresAt = now()->addHour();

        // Generate Signed URL
        $signedUrl = URL::temporarySignedRoute(
            'portal_warga.verify', $expiresAt, ['phone' => $phoneSend]
        );

        // Single-Use Token Tracking
        $urlParts = parse_url($signedUrl);
        parse_str($urlParts['query'] ?? '', $queryParts);
        $signature = $queryParts['signature'] ?? null;

        if ($signature) {
            PortalLoginToken::create([
                'phone' => $phoneSend,
                'signature' => $signature,
                'expires_at' => $expiresAt,
            ]);

            return $this->sendWhatsApp($phoneSend, $this->buildMagicLinkMessage($name, $signedUrl));
        }

        return false;
    }

    /**
     * Send generic WhatsApp message using relaxed bot check
     */
    public function sendWhatsApp(string $phone, string $message): bool
    {
        try {
            $wahaSettings = WahaN8nSetting::getSettings();
            
            // RELAXED CHECK: Only check if bot is enabled. 
            if (!$wahaSettings || !$wahaSettings->bot_enabled) {
                Log::warning('PortalService: Bot is disabled or settings missing.');
                return false;
            }

            $wahaUrl = $wahaSettings->waha_api_url;
            $wahaKey = $wahaSettings->waha_api_key;
            $session = $wahaSettings->waha_session_name ?? 'default';

            if ($wahaUrl) {
                $headers = ['Content-Type' => 'application/json'];
                if ($wahaKey) $headers['X-Api-Key'] = $wahaKey;

                $response = Http::withHeaders($headers)->timeout(8)->post(rtrim($wahaUrl, '/') . '/api/sendText', [
                    'session' => $session,
                    'chatId' => $this->formatForWA($phone) . '@c.us',
                    'text' => $message,
                ]);

                if (!$response->successful()) {
                    Log::error('PortalService: WAHA API Error: ' . $response->body());
                    return false;
                }

                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('PortalService: WhatsApp failed to send: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Complaint Confirmation WhatsApp
     */
    public function sendComplaintConfirmation(PublicService $service): bool
    {
        $kategori = $service->category === 'pengaduan' ? '📢 Pengaduan' : '📋 Permohonan Layanan';
        
        $msg = "✅ *Laporan Diterima!*\n\n";
        $msg .= "Halo *{$service->nama_pemohon}*, laporan Anda telah berhasil kami terima.\n\n";
        $msg .= "━━━━━━━━━━━━━━━━━\n";
        $msg .= "🔑 *PIN Lacak:* `{$service->tracking_code}`\n";
        $msg .= "📁 *Jenis:* {$kategori}\n";
        $msg .= "🕐 *Waktu:* " . now()->format('d/m/Y H:i') . " WIB\n";
        $msg .= "━━━━━━━━━━━━━━━━━\n\n";
        $msg .= "Simpan PIN di atas untuk melacak status laporan Anda.\n";
        $msg .= "Reply ke nomor ini atau kunjungi:\n";
        $msg .= route('public.tracking') . "?q={$service->tracking_code}\n\n";
        $msg .= "_Pesan ini dikirim otomatis oleh sistem._";

        return $this->sendWhatsApp($service->whatsapp, $msg);
    }

    protected function buildMagicLinkMessage(string $name, string $url): string
    {
        return "🔐 *Pusat Kendali Profil Warga*\n\n" .
               "Halo *{$name}*,\n" .
               "Seseorang (atau Anda sendiri) meminta akses untuk masuk ke Dasbor Ekonomi & UMKM Kecamatan Digital.\n\n" .
               "Klik tautan aman di bawah ini untuk mengelola profil *UMKM* atau *Jasa/Pekerjaan* Anda secara langsung tanpa PIN maupun Password:\n" .
               "{$url}\n\n" .
               "_PENTING: Tautan ini akan mengelola data warga Anda di aplikasi kecamatan. JANGAN BAGIKAN link ini ke siapapun._";
    }
}
