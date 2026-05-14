<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Models\Hub\HubDistrict;
use App\Models\PublicService;
use App\Services\Hub\DistrictConnectionService;

class DashboardController extends Controller
{
    public function loginForm()
    {
        return view('hub.auth.login');
    }

    public function index(DistrictConnectionService $connector)
    {
        $districts        = HubDistrict::all();
        $total_districts  = $districts->count();
        $active_districts = $districts->where('is_active', true)->count();

        // Statistik dari DB lokal (data kecamatan yang sama server)
        $total_services  = PublicService::count();
        $pending_services = PublicService::where('status', 'menunggu')
                            ->orWhere('status', 'pending')->count();
        $done_services   = PublicService::whereIn('status', ['selesai', 'done'])->count();

        // Statistik Live dari tiap kecamatan (via koneksi dinamis)
        $district_stats = $connector->getAllStats();

        // Hitung agregat dari semua kecamatan yang berhasil terkoneksi
        $global_total    = 0;
        $global_pending  = 0;
        $global_done     = 0;
        $reachable_count = 0;

        foreach ($district_stats as $stat) {
            if ($stat['is_reachable']) {
                $global_total   += $stat['total_services'] ?? 0;
                $global_pending += $stat['pending']        ?? 0;
                $global_done    += $stat['done']           ?? 0;
                $reachable_count++;
            }
        }

        // Jika ada koneksi ke kecamatan lain, gunakan global stats
        // Jika tidak (lokal only), gunakan data DB lokal
        if ($reachable_count > 0) {
            $total_services   = $global_total;
            $pending_services = $global_pending;
            $done_services    = $global_done;
        }

        // Aktivitas terbaru dari DB lokal
        $recent_activities = PublicService::select('id', 'nama_pemohon', 'jenis_layanan', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('hub.dashboard', compact(
            'districts',
            'total_districts',
            'active_districts',
            'total_services',
            'pending_services',
            'done_services',
            'recent_activities',
            'district_stats'
        ));
    }
}
