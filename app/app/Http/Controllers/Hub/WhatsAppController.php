<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Models\Hub\HubAiConfig;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    /**
     * Tampilkan halaman pengaturan WhatsApp Hub.
     */
    public function index()
    {
        $configs = HubAiConfig::orderBy('key')->get();
        
        // Check WAHA Status
        $waha_url = config('services.waha.url', 'http://localhost:3000');
        $is_online = false;
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(1)->get($waha_url . '/health');
            $is_online = $response->successful();
        } catch (\Exception $e) {
            $is_online = false;
        }

        return view('hub.whatsapp.index', compact('configs', 'is_online'));
    }

    /**
     * Simpan atau update instruksi AI global.
     */
    public function storeConfig(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        HubAiConfig::updateOrCreate(
            ['key' => $validated['key']],
            ['value' => $validated['value']]
        );

        return redirect()->back()->with('success', 'Instruksi AI Global berhasil diperbarui!');
    }
}
