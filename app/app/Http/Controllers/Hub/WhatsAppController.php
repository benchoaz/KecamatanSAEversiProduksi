<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Models\Hub\HubAiConfig;
use App\Models\Hub\HubDistrict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WhatsAppController extends Controller
{
    public function index()
    {
        $configs   = HubAiConfig::orderBy('key')->get();
        $districts = HubDistrict::orderBy('name')->get();

        // Check WAHA Status
        $waha_url  = config('services.waha.url', 'http://localhost:3000');
        $is_online = false;
        try {
            $response  = Http::timeout(2)->get($waha_url . '/api/health');
            $is_online = $response->successful();
        } catch (\Exception $e) {
            $is_online = false;
        }

        // Statistik real dari hub_message_logs
        $today       = now()->toDateString();
        $stats_today = DB::table('hub_message_logs')
            ->whereDate('created_at', $today)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN handler_layer = 'l1' THEN 1 ELSE 0 END) as l1_count,
                SUM(CASE WHEN handler_layer = 'l2' THEN 1 ELSE 0 END) as l2_count,
                SUM(CASE WHEN handler_layer = 'l3' THEN 1 ELSE 0 END) as l3_count,
                SUM(CASE WHEN is_complaint = true THEN 1 ELSE 0 END) as complaints,
                ROUND(AVG(response_time_ms)) as avg_response_ms
            ")
            ->first();

        // Session aktif
        $active_sessions = DB::table('hub_wa_sessions')
            ->where('is_active', true)
            ->count();

        // Breakdown per kecamatan hari ini
        $district_stats = DB::table('hub_message_logs')
            ->whereDate('hub_message_logs.created_at', $today)
            ->join('hub_districts', 'hub_message_logs.hub_district_id', '=', 'hub_districts.id')
            ->groupBy('hub_districts.id', 'hub_districts.name')
            ->selectRaw('hub_districts.name, COUNT(*) as total')
            ->orderByDesc('total')
            ->get();

        return view('hub.whatsapp.index', compact(
            'configs', 'districts', 'is_online',
            'stats_today', 'active_sessions', 'district_stats', 'waha_url'
        ));
    }

    /**
     * Update konfigurasi WAHA & AI per-kecamatan.
     */
    public function updateDistrictConfig(Request $request, HubDistrict $district)
    {
        $validated = $request->validate([
            'waha_session_name' => 'nullable|string|max:100',
            'operator_phone'    => 'nullable|string|max:20',
            'ai_enabled'        => 'boolean',
            'n8n_webhook_url'   => 'nullable|url',
        ]);

        $district->update($validated);
        return redirect()->back()->with('success', "Konfigurasi {$district->name} berhasil disimpan.");
    }

    /**
     * Simpan instruksi AI global.
     */
    public function storeConfig(Request $request)
    {
        $validated = $request->validate([
            'key'   => 'required|string',
            'value' => 'required|string',
        ]);

        HubAiConfig::updateOrCreate(
            ['key'   => $validated['key']],
            ['value' => $validated['value']]
        );

        return redirect()->back()->with('success', 'Instruksi AI Global berhasil diperbarui!');
    }
}
