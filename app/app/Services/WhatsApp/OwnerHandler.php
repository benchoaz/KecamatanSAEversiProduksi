<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsappSession;
use App\Models\UmkmLocal;
use App\Models\Loker;

class OwnerHandler
{
    /**
     * Initiate owner toggle flow (request PIN)
     */
    public function initiate(string $phone): array
    {
        $baseUrl = env('PUBLIC_BASE_URL', config('app.url', 'https://babette-nonslanderous-randi.ngrok-free.dev'));
        
        return [
            'success' => true,
            'intent' => 'owner_portal_link',
            'reply' => "🌟 *Halo Warga Kreatif!* 🌟\n\n" .
                "Ingin mengubah jam buka Jasa atau mengelola produk UMKM Anda? Sekarang makin gampang lho!\n\n" .
                "Klik tautan pribadi di bawah ini untuk akses cepat ke Dashboard Anda:\n" .
                "🌐 {$baseUrl}/portal-warga/masuk\n\n" .
                "Butuh bantuan lain? Ketik *MENU* kapan saja ya! 😊",
            'state_update' => null,
        ];
    }

    /**
     * Handle Forgot PIN - Redirect to PIN-less portal
     */
    public function handleForgotOwnerPin(string $phone): array
    {
        $baseUrl = env('PUBLIC_BASE_URL', config('app.url', 'https://babette-nonslanderous-randi.ngrok-free.dev'));

        return [
            'success' => true,
            'intent' => 'owner_lupa_pin',
            'reply' => "📱 *Dashboard Manajemen Mandiri*\n\n" .
                "Kelola profil toko atau jasa Anda kapan saja langsung dari HP melalui tautan di bawah ini:\n\n" .
                "🌐 {$baseUrl}/portal-warga/masuk\n\n" .
                "Klik tautan tersebut untuk akses tanpa repot. Ketik *MENU* untuk kembali.",
            'state_update' => null,
        ];
    }

    /**
     * Quick toggle holiday status for all assets owned by this phone
     */
    public function toggleHolidayStatus(string $phone, string $action): array
    {
        $action = strtolower($action);
        $isHoliday = ($action === 'libur');
        $newStatusLabel = $isHoliday ? 'DILIBURKAN 🔴' : 'DIBUKA KEMBALI 🟢';

        // Normalize phone for searching
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $basePhone = $cleanPhone;
        if (str_starts_with($cleanPhone, '62')) {
            $basePhone = substr($cleanPhone, 2);
        } elseif (str_starts_with($cleanPhone, '0')) {
            $basePhone = substr($cleanPhone, 1);
        }
        $likeClause = '%' . ltrim($basePhone, '0') . '%';

        // Update all related assets
        $umkmCount = \App\Models\Umkm::where('no_wa', 'like', $likeClause)->update(['is_on_holiday' => $isHoliday]);
        $jasaCount = \App\Models\WorkDirectory::where('contact_phone', 'like', $likeClause)->update(['is_on_holiday' => $isHoliday]);
        $localCount = \App\Models\UmkmLocal::where('contact_wa', 'like', $likeClause)->update(['is_on_holiday' => $isHoliday]);

        $total = $umkmCount + $jasaCount + $localCount;

        if ($total === 0) {
            return [
                'success' => true,
                'intent' => 'owner_not_found',
                'reply' => "Waduh, sepertinya nomor WhatsApp Anda belum terdaftar di sistem kami nih. 😊\n\nYuk, daftarkan usaha atau jasa Anda terlebih dahulu melalui menu *KELOLA PROFIL* agar bisa dikelola lewat sini!",
                'state_update' => null,
            ];
        }

        $reply = "✅ *BERHASIL DIUPDATE!* \n\n";
        $reply .= "Sip! Seluruh layanan atau toko Anda sekarang sudah {$newStatusLabel}.\n";
        
        if ($isHoliday) {
            $reply .= "\nStatus [LIBUR] akan tampil di hasil pencarian warga agar mereka tahu Anda sedang tidak melayani.";
        } else {
            $reply .= "\nStatus [Lagi Buka] akan tampil kembali di hasil pencarian warga.";
        }

        $reply .= "\n\nKetik *MENU* untuk kembali.";

        return [
            'success' => true,
            'intent' => 'owner_holiday_toggled',
            'reply' => $reply,
            'state_update' => null,
        ];
    }

    /**
     * Error response
     */
    protected function errorResponse(): array
    {
        return [
            'success' => false,
            'intent' => 'error',
            'reply' => "Terjadi kesalahan sistem. Silakan coba lagi nanti.",
            'state_update' => null,
        ];
    }
}
