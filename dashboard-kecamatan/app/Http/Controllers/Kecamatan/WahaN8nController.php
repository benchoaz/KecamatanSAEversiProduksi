<?php

namespace App\Http\Controllers\Kecamatan;

use App\Http\Controllers\Controller;
use App\Models\AppProfile;
use App\Models\WahaN8nSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WahaN8nController extends Controller
{
    /**
     * Display WAHA/n8n settings page
     * Simplified - hanya untuk update nomor bot WhatsApp
     */
    public function index()
    {
        $settings = WahaN8nSetting::getSettings() ?? new WahaN8nSetting();

        return view('kecamatan.settings.waha-n8n', compact('settings'));
    }

    /**
     * Update bot settings - hanya nomor dan enable/disable
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'bot_number' => 'nullable|string|max:20',
            'bot_enabled' => 'nullable|boolean',
        ]);

        // Normalize bot_enabled (checkbox sends nothing when unchecked)
        $validated['bot_enabled'] = $request->has('bot_enabled') ? true : false;

        // Normalize phone number: accept 08xxx or 628xxx, store as 628xxx for wa.me links
        if (!empty($validated['bot_number'])) {
            $phone = preg_replace('/[^0-9]/', '', $validated['bot_number']);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            } elseif (!str_starts_with($phone, '62')) {
                $phone = '62' . $phone;
            }
            $validated['bot_number'] = $phone;
        }

        $settings = WahaN8nSetting::first();

        if ($settings) {
            $settings->update($validated);
        } else {
            $settings = WahaN8nSetting::create($validated);
        }

        // Sync bot_number to app_profiles.whatsapp_bot_number (used by landing page)
        if (!empty($validated['bot_number'])) {
            AppProfile::query()->update(['whatsapp_bot_number' => $validated['bot_number']]);
            Cache::forget('app_profile_global');
        }

        // Clear WAHA settings cache
        WahaN8nSetting::clearCache();

        return redirect()
            ->route('kecamatan.settings.waha-n8n.index')
            ->with('success', 'Pengaturan bot berhasil disimpan.');
    }
}
