<?php

namespace App\Services\WhatsApp;

use App\Models\PublicService;
use Illuminate\Support\Facades\Cache;

class StatusHandler
{
    /**
     * Cache TTL in seconds (5 minutes)
     */
    private const CACHE_TTL = 300;

    /**
     * Handle status check request
     */
    public function handle($phone, ?string $query = null): array
    {
        // Normalize phone to string
        if (!is_string($phone)) {
            if (is_array($phone)) {
                $phone = reset($phone);
            }
            $phone = (string) $phone;
        }
        // If a specific query (PIN or UUID) is provided
        if ($query) {
            // Try cache first for PIN/UUID lookup
            $cacheKey = $this->getCacheKey('pin', $query);
            $cached = Cache::get($cacheKey);

            if ($cached) {
                return $cached;
            }

            $service = PublicService::where('tracking_code', $query)
                ->orWhere('uuid', $query)
                ->first();

            if ($service) {
                $result = [
                    'success' => true,
                    'intent' => 'status',
                    'reply' => $this->formatSingleStatus($service),
                    'state_update' => 'ADM_SUBMENU',
                ];

                // Cache the result
                Cache::put($cacheKey, $result, self::CACHE_TTL);

                return $result;
            }
        }

        // Clean phone number (remove +, spaces, etc.)
        $cleanPhone = $this->normalizePhone($phone);

        // Try cache for phone number lookup
        $cacheKey = $this->getCacheKey('phone', $cleanPhone);
        $cached = Cache::get($cacheKey);

        if ($cached) {
            return $cached;
        }

        // Search for public services by phone number (multiple matching strategies)
        $services = $this->findByPhone($cleanPhone);

        if ($services->isEmpty()) {
            $result = [
                'success' => true,
                'intent' => 'status',
                'reply' => $this->formatNotFound($phone),
                'state_update' => 'ADM_SUBMENU',
            ];

            // Cache negative result for shorter time (2 minutes)
            Cache::put($cacheKey, $result, 120);

            return $result;
        }

        if ($services->count() === 1) {
            $service = $services->first();
            $result = [
                'success' => true,
                'intent' => 'status',
                'reply' => $this->formatSingleStatus($service),
                'state_update' => 'ADM_SUBMENU',
            ];
        } else {
            // Multiple services found
            $result = [
                'success' => true,
                'intent' => 'status',
                'reply' => $this->formatMultipleStatus($services),
                'state_update' => 'ADM_SUBMENU',
            ];
        }

        // Cache the result
        Cache::put($cacheKey, $result, self::CACHE_TTL);

        return $result;
    }

    /**
     * Handle forgot PIN (LUPA PIN) request
     */
    public function handleForgotPin($phone): array
    {
        // Normalize phone to string
        if (!is_string($phone)) {
            if (is_array($phone)) {
                $phone = reset($phone);
            }
            $phone = (string) $phone;
        }

        $cleanPhone = $this->normalizePhone($phone);

        // Find all services by phone number
        $services = $this->findByPhone($cleanPhone);

        if ($services->isEmpty()) {
            return [
                'success' => true,
                'intent' => 'lupa_pin',
                'reply' => $this->formatNoServicesForForgotPin($phone),
                'state_update' => 'ADM_SUBMENU',
            ];
        }

        return [
            'success' => true,
            'intent' => 'lupa_pin',
            'reply' => $this->formatForgotPinResponse($services),
            'state_update' => 'ADM_SUBMENU',
        ];
    }

    /**
     * Normalize phone number to standard format
     */
    protected function normalizePhone($phone): string
    {
        // Handle if phone is an array or object
        if (!is_string($phone)) {
            if (is_array($phone)) {
                $phone = reset($phone); // Get first element
            }
            $phone = (string) $phone;
        }

        // Remove all non-numeric characters
        $clean = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 62, keep it (Indonesia format with country code)
        // If starts with 0, convert to 62
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }

        return $clean;
    }

    /**
     * Find services by phone number with multiple matching strategies
     */
    protected function findByPhone(string $phone): \Illuminate\Database\Eloquent\Collection
    {
        if (empty($phone)) {
            return collect();
        }

        // Try exact match first
        $services = PublicService::where('whatsapp', $phone)
            ->latest()
            ->get();

        if ($services->isNotEmpty()) {
            return $services;
        }

        // Try suffix match (last 10 digits) - now using indexed column
        if (strlen($phone) >= 10) {
            $suffix = substr($phone, -10);
            $services = PublicService::where('whatsapp_suffix', $suffix)
                ->latest()
                ->get();

            if ($services->isNotEmpty()) {
                return $services;
            }
        }

        // Fallback: LIKE search (slower)
        if (strlen($phone) >= 10) {
            $suffix = substr($phone, -10);
            $services = PublicService::where('whatsapp', 'LIKE', "%{$suffix}")
                ->latest()
                ->get();
        }

        return $services ?? collect();
    }

    /**
     * Generate cache key
     */
    protected function getCacheKey(string $type, string $identifier): string
    {
        return "whatsapp_status:{$type}:" . md5($identifier);
    }

    /**
     * Format single service status
     */
    protected function formatSingleStatus(PublicService $service): string
    {
        $status = "STATUS BERKAS LAYANAN\n\n";
        $status .= "Jenis Layanan: {$service->jenis_layanan}\n";
        $status .= "ID: " . substr($service->uuid, 0, 8) . "...\n";
        $status .= "PIN Lacak: *{$service->tracking_code}*\n";
        $status .= "Status: " . $this->getStatusBadge($service->status) . "\n";
        $status .= "Tanggal: {$service->created_at->format('d/m/Y')}\n";

        $response = $service->effective_public_response;
        if ($response) {
            $status .= "\nTanggapan Petugas:\n{$response}";
        }

        if ($service->completion_type === 'digital' && $service->result_file_path) {
            $status .= "\n\nDokumen Selesai: Silakan cek di website atau hubungi admin.";
        }

        return $status;
    }

    /**
     * Format multiple services status
     */
    protected function formatMultipleStatus($services): string
    {
        $status = "DAFTAR BERKAS LAYANAN ANDA\n\n";
        $status .= "Ditemukan {$services->count()} berkas:\n\n";

        foreach ($services as $index => $service) {
            $num = $index + 1;
            $status .= "{$num}. {$service->jenis_layanan}\n";
            $status .= "   ID: " . substr($service->uuid, 0, 8) . "...\n";
            $status .= "   PIN Lacak: *{$service->tracking_code}*\n";
            $status .= "   Status: " . $this->getStatusBadge($service->status) . "\n";
            $status .= "   Tanggal: {$service->created_at->format('d/m/Y')}\n\n";
        }

        $status .= "Gunakan PIN Lacak (6 angka) untuk melihat detail lebih lengkap di website atau ketik langsung PIN tersebut di sini.";

        return $status;
    }

    /**
     * Get status badge with emoji
     */
    protected function getStatusBadge(string $status): string
    {
        // statuses from PublicService model or controller logic
        return match (strtolower($status)) {
            'pending', 'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'diproses' => 'Sedang Diproses',
            'selesai' => 'Selesai (Siap Diambil/Download)',
            'ditolak' => 'Ditolak/Perlu Perbaikan',
            default => ucfirst($status),
        };
    }

    /**
     * Format response when service not found
     */
    protected function formatNotFound(string $phone): string
    {
        return "Berkas Tidak Ditemukan\n\n" .
            "Tidak ditemukan berkas layanan yang terdaftar dengan nomor {$phone}.\n\n" .
            "Kemungkinan:\n" .
            "- Nomor WA yang digunakan belum terdaftar\n" .
            "- Atau gunakan PIN Lacak Anda langsung\n\n" .
            "Cara Cek Status:\n" .
            "1. Ketik: STATUS (untuk lihat semua berkas)\n" .
            "2. Atau ketik: STATUS [PIN]\n" .
            "   Contoh: STATUS 082231\n\n" .
            "---\n" .
            "Ketik: MENU untuk kembali";
    }

    /**
     * Format response for forgot PIN - no services found
     */
    protected function formatNoServicesForForgotPin(string $phone): string
    {
        return "Nomor Tidak Terdaftar\n\n" .
            "Nomor {$phone} belum terdaftar dalam sistem kami.\n\n" .
            "Langkah Selanjutnya:\n" .
            "Silakan mengajukan layanan baru melalui:\n" .
            "- Website: [lacak-berkas]\n" .
            "- Atau hubungi petugas\n\n" .
            "Anda akan mendapatkan PIN Lacak setelah pengajuan\n" .
            "diterima.\n\n" .
            "---\n" .
            "Ketik: MENU untuk kembali";
    }

    /**
     * Format response for forgot PIN - services found
     */
    protected function formatForgotPinResponse($services): string
    {
        $reply = "PENCARIAN PIN LACAK\n\n";
        $reply .= "Berikut daftar PIN Lacak untuk nomor Anda:\n\n";

        foreach ($services as $service) {
            $reply .= "{$service->jenis_layanan}\n";
            $reply .= "   PIN: {$service->tracking_code}\n";
            $reply .= "   Status: " . $this->getStatusBadge($service->status) . "\n";
            $reply .= "   Tanggal: {$service->created_at->format('d/m/Y')}\n\n";
        }

        $reply .= "Gunakan PIN di atas untuk:\n" .
            "- Cek status lengkap: STATUS [PIN]\n" .
            "- Contoh: STATUS {$services->first()->tracking_code}\n\n" .
            "---\n" .
            "Ketik: MENU untuk kembali";

        return $reply;
    }
}
