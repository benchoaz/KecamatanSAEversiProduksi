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
        return view('hub.whatsapp.index', compact('configs'));
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
