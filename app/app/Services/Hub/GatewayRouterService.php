<?php

namespace App\Services\Hub;

use App\Models\Hub\HubDistrict;
use App\Models\Hub\HubWaSession;
use Illuminate\Support\Facades\Log;

/**
 * Kabupaten Gateway Router Service
 * Bertanggung jawab mengarahkan trafik (WA/Web) ke kecamatan yang tepat.
 */
class GatewayRouterService
{
    /**
     * Tentukan kecamatan tujuan berdasarkan nomor telepon WA.
     */
    public function getDistrictByPhone(string $phoneNumber): ?HubDistrict
    {
        $session = HubWaSession::where('phone_number', $phoneNumber)
            ->where('is_active', true)
            ->first();

        return $session ? $session->district : null;
    }

    /**
     * Hubungkan nomor telepon ke kecamatan tertentu (sticky session).
     */
    public function setPhoneDistrict(string $phoneNumber, string $districtId): bool
    {
        try {
            HubWaSession::updateOrCreate(
                ['phone_number' => $phoneNumber],
                [
                    'hub_district_id' => $districtId,
                    'last_interaction_at' => now(),
                    'is_active' => true
                ]
            );
            return true;
        } catch (\Exception $e) {
            Log::error("GatewayRouter: Failed to set phone district: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil daftar seluruh kecamatan aktif untuk routing.
     */
    public function getActiveDistricts()
    {
        return HubDistrict::where('is_active', true)->get();
    }
}
