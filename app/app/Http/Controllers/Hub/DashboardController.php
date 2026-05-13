<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Models\Hub\HubDistrict;
use App\Models\PublicService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function loginForm()
    {
        return view('hub.auth.login');
    }

    public function index()
    {
        $districts = HubDistrict::all();
        $total_districts = $districts->count();
        $active_districts = $districts->where('is_active', true)->count();

        // Statistik Global: Agregasi dari database lokal
        $total_services = PublicService::count();
        $pending_services = PublicService::where('status', 'pending')->count();
        $done_services = PublicService::where('status', 'selesai')->orWhere('status', 'done')->count();

        // Aktivitas terbaru (10 terakhir dari semua kecamatan)
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
            'recent_activities'
        ));
    }
}
