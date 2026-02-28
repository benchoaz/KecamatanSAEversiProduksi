<?php

namespace App\Http\Controllers;

use App\Models\WorkDirectory;
use App\Models\Desa;
use App\Models\PublicService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EconomyController extends Controller
{
    /**
     * Display listing of economy (Jobs & UMKM)
     */
    public function index(Request $request)
    {
        $query = WorkDirectory::public();

        // Filter by category if provided
        if ($request->filled('kategori')) {
            $query->where('job_category', $request->kategori);
        }

        // Filter by type if provided
        if ($request->filled('tipe')) {
            $query->where('job_type', $request->tipe);
        }

        // Search
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%")
                    ->orWhere('job_title', 'like', "%{$search}%")
                    ->orWhere('service_area', 'like', "%{$search}%");
            });
        }

        $workItems = $query->latest()->paginate(12);

        // Get categories for filter
        $categories = WorkDirectory::public()
            ->select('job_category')
            ->distinct()
            ->pluck('job_category');

        // Fetch UMKM Data for the "Etalase" tab
        $umkms = \App\Models\UmkmLocal::where('is_active', true)->latest()->limit(8)->get();

        $defaultTab = $request->get('tab', 'jasa');

        return view('economy.index', compact('workItems', 'categories', 'umkms', 'defaultTab'));
    }

    /**
     * Display single work directory item
     */
    public function show($id)
    {
        $workItem = WorkDirectory::public()->findOrFail($id);

        // Get related items (same category, exclude current)
        $relatedItems = WorkDirectory::public()
            ->where('job_category', $workItem->job_category)
            ->where('id', '!=', $workItem->id)
            ->limit(3)
            ->get();

        return view('economy.show', compact('workItem', 'relatedItems'));
    }

    /**
     * Show registration form for Pekerjaan & Jasa
     */
    public function create()
    {
        $desas = Desa::orderBy('nama_desa')->get();
        $categories = WorkDirectory::getCategories();
        $jobTypes = ['jasa' => 'Jasa', 'transportasi' => 'Transportasi', 'keliling' => 'Keliling', 'harian' => 'Harian'];

        return view('economy.create', compact('desas', 'categories', 'jobTypes'));
    }

    /**
     * Store new Pekerjaan & Jasa registration
     */
    public function store(Request $request)
    {
        $request->validate([
            'display_name' => 'required|string|max:255',
            'job_category' => 'required|string',
            'job_type' => 'required|string|in:jasa,transportasi,keliling,harian',
            'job_title' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'service_area' => 'nullable|string|max:255',
            'service_time' => 'nullable|string|max:100',
            'short_description' => 'nullable|string|max:500',
        ]);

        // Create WorkDirectory entry
        $workDir = WorkDirectory::create([
            'display_name' => $request->display_name,
            'job_category' => $request->job_category,
            'job_type' => $request->job_type,
            'job_title' => $request->job_title,
            'contact_phone' => $request->contact_phone,
            'service_area' => $request->service_area,
            'service_time' => $request->service_time,
            'short_description' => $request->short_description,
            'consent_public' => true,
            'status' => 'pending',
            'data_source' => 'web_form'
        ]);

        // Create Public Service entry for Inbox
        PublicService::create([
            'uuid' => (string) Str::uuid(),
            'nama_pemohon' => $workDir->display_name,
            'desa_id' => $request->desa_id,
            'jenis_layanan' => 'Pendaftaran Pekerjaan & Jasa',
            'uraian' => "Pendaftaran Pekerjaan/Jasa Baru: {$workDir->job_title} ({$workDir->job_category}). Atas nama: {$workDir->display_name}. Kontak: {$workDir->contact_phone}.",
            'whatsapp' => $workDir->contact_phone,
            'status' => PublicService::STATUS_MENUNGGU,
            'category' => PublicService::CATEGORY_PEKERJAAN,
            'source' => 'web_form'
        ]);

        return redirect()->route('economy.index', ['tab' => 'jasa'])->with('success', 'Terima kasih. Data pekerjaan/jasa Anda akan ditampilkan setelah diverifikasi.');
    }
}
