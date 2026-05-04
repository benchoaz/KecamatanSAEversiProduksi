<?php

namespace App\Services\WhatsApp;

use App\Models\UmkmLocal;

class UmkmHandler
{
    /**
     * Search UMKM by query
     */
    public function search(string $query): array
    {
        if (empty($query)) {
            return [
                'success' => true,
                'intent' => 'umkm',
                'reply' => "Silakan masukkan kata kunci pencarian.\nContoh: *umkm bakso*",
                'state_update' => null,
            ];
        }

        // Search verified and active UMKM only
        $umkms = UmkmLocal::where('module', UmkmLocal::MODULE_UMKM)
            ->where('is_verified', true)
            ->where('is_active', true)
            ->where('is_flagged', false)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('product', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->limit(5)
            ->get();

        if ($umkms->isEmpty()) {
            return [
                'success' => true,
                'intent' => 'umkm',
                'reply' => "😔 *Maaf*, tidak ditemukan UMKM dengan kata kunci \"*{$query}*\".\n\n" .
                    "Silakan coba kata kunci lain atau ketik *MENU* atau *0* untuk kembali.",
                'state_update' => 'WAITING_UMKM_SEARCH',
            ];
        }

        return [
            'success' => true,
            'intent' => 'umkm',
            'reply' => $this->formatResults($umkms, $query),
            'state_update' => null,
        ];
    }

    /**
     * Format search results
     */
    protected function formatResults($umkms, string $query): string
    {
        $baseUrl = $this->getPublicUrl();
        $message = "🏛️ *HASIL PENCARIAN UMKM*\n";
        $message .= "Kata kunci: \"*{$query}*\"\n";
        $message .= "Ditemukan {$umkms->count()} UMKM:\n\n";

        foreach ($umkms as $index => $umkm) {
            $num = $index + 1;
            $opStatus = $umkm->operational_status;
            $statusPrefix = $opStatus['is_open'] ? "🟢" : "🔴";
            
            $message .= "{$num}. {$statusPrefix} *{$umkm->name}*\n";
            $message .= "   _{$opStatus['label']}_\n";
            
            if ($umkm->product) {
                $message .= "   🛍️ Produk: {$umkm->product}\n";
            }

            if ($umkm->contact_wa) {
                $message .= "   📱 WA: {$umkm->contact_wa}\n";
            }

            // Link to store profile on website
            $storeUrl = $baseUrl . "/ekonomi?tab=produk&search=" . urlencode($umkm->name);
            $message .= "   🔗 *Lihat Foto Produk:*\n   {$storeUrl}\n\n";
        }

        $message .= "━━━━━━━━━━━━━━━━━\n";
        $message .= "💡 *Tips:* Klik link di atas untuk melihat katalog produk lengkap.\n\n";
        $message .= "_Ketik MENU untuk kembali._";

        return $message;
    }

    protected function getPublicUrl(): string
    {
        $profile = \App\Models\AppProfile::first();
        if ($profile && !empty($profile->public_url)) {
            return rtrim($profile->public_url, '/');
        }
        return rtrim(env('PUBLIC_BASE_URL', config('app.url')), '/');
    }
}
