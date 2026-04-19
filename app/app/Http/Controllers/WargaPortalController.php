<?php

namespace App\Http\Controllers;

use App\Models\Umkm;
use App\Models\WorkDirectory;
use App\Models\UmkmLocal;
use App\Models\WahaN8nSetting;
use App\Models\PortalLoginToken;
use App\Services\PortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WargaPortalController extends Controller
{
    protected $portalService;

    public function __construct(PortalService $portalService)
    {
        $this->portalService = $portalService;
    }
    public function login()
    {
        return view('public.warga.login');
    }

    public function requestAccess(Request $request)
    {
        $request->validate([
            'no_wa' => 'required|string|max:20',
        ]);

        // Use service to handle the logic
        $this->portalService->requestAccess($request->no_wa);

        // ALWAYS return success view to prevent account enumeration (identity harvesting)
        return view('public.warga.login_success', ['phone' => $request->no_wa]);
    }

    public function verify(Request $request, $phone)
    {
        // 1. Standard Laravel Signature Verification
        if (! $request->hasValidSignature()) {
            return redirect()->route('portal_warga.login')->with('error', 'Link akses tidak valid atau sudah kadaluarsa. Silakan request link baru.');
        }

        // 2. Single-Use Verification
        $signature = $request->query('signature');
        $loginToken = PortalLoginToken::where('signature', $signature)->first();

        if (!$loginToken || !$loginToken->isValid()) {
            return redirect()->route('portal_warga.login')->with('error', 'Link ini sudah pernah digunakan atau tidak valid. Silakan request link baru.');
        }

        // 3. Mark as Used (Invalidate for future clicks)
        $loginToken->markAsUsed();

        // Simpan sesi login warga
        session(['warga_phone' => $phone]);

        return redirect()->route('portal_warga.dashboard')->with('success', 'Berhasil terautentikasi.');
    }

    public function dashboard(Request $request)
    {
        $phone = session('warga_phone');
        
        if (!$phone) {
            return redirect()->route('portal_warga.login')->with('error', 'Sesi berakhir. Silakan login kembali.');
        }

        // Get matching phone numbers via service
        $basePhone = $this->portalService->normalizePhone($phone);
        $likeClause = '%' . $basePhone . '%';

        // Fetch User's Assets
        $umkms = Umkm::where('no_wa', 'like', $likeClause)->get();
        $jasas = WorkDirectory::where('contact_phone', 'like', $likeClause)->get();
        $umkmLocals = UmkmLocal::where('contact_wa', 'like', $likeClause)->get();

        // Group UmkmLocal by phone number to handle shops with multiple products
        $consolidatedUmkmLocals = $umkmLocals->groupBy('contact_wa')->map(function ($group) {
            $first = $group->first();
            $first->product_count = $group->count(); // Add count for UI
            $first->all_products = $group->pluck('product')->join(', ');
            return $first;
        })->values();

        // Merge structured assets and prevent duplicates
        $allAssets = collect();
        
        // 1. Process UMKMs (and attach local product counts if any)
        foreach ($umkms as $item) {
            // Find corresponding local products by name
            $matchedLocal = $consolidatedUmkmLocals->first(function($loc) use ($item) {
                return strtolower(trim($loc->name)) === strtolower(trim($item->nama_usaha));
            });

            if ($matchedLocal) {
                // Attach product counts to the main UMKM item
                $item->product_count = $matchedLocal->product_count;
                $item->all_products = $matchedLocal->all_products;
                // Remove from local array so it isn't rendered twice
                $consolidatedUmkmLocals = $consolidatedUmkmLocals->reject(fn($loc) => $loc->id === $matchedLocal->id);
            }

            $allAssets->push([
                'data' => $item, 
                'type' => 'umkm', 
                'name' => $item->nama_usaha,
                'name_cooldown' => $item->name_updated_at && $item->name_updated_at->diffInDays(now()) < 30 ? 30 - $item->name_updated_at->diffInDays(now()) : 0
            ]);
        }

        // 2. Add remaining standalone UMKMLocals
        foreach ($consolidatedUmkmLocals as $item) {
            $allAssets->push([
                'data' => $item, 
                'type' => 'umkm_local', 
                'name' => $item->name,
                'name_cooldown' => $item->name_updated_at && $item->name_updated_at->diffInDays(now()) < 30 ? 30 - $item->name_updated_at->diffInDays(now()) : 0
            ]);
        }

        // 3. Add Jasa
        foreach ($jasas as $item) {
            $allAssets->push([
                'data' => $item, 
                'type' => 'jasa', 
                'name' => $item->job_title,
                'name_cooldown' => $item->name_updated_at && $item->name_updated_at->diffInDays(now()) < 30 ? 30 - $item->name_updated_at->diffInDays(now()) : 0
            ]);
        }

        return view('public.warga.dashboard', [
            'allAssets' => $allAssets,
            'umkms' => $umkms,
            'jasas' => $jasas,
            'umkmLocals' => $umkmLocals, // Keep raw if needed elsewhere
            'phone' => $phone
        ]);
    }

    public function bridgeJasa($id)
    {
        $phone = session('warga_phone');
        if (!$phone) return redirect()->route('portal_warga.login');

        // Verify this $id actually belongs to the user via service normalization
        $basePhone = $this->portalService->normalizePhone($phone);
        
        $jasa = WorkDirectory::findOrFail($id);
        $jasaPhone = $this->portalService->normalizePhone($jasa->contact_phone);
        
        // Match checking
        if (!str_contains($jasaPhone, $basePhone)) {
             return redirect()->route('portal_warga.dashboard')->with('error', 'Anda tidak memiliki akses ke jasa ini.');
        }

        // Grant access
        session(['manage_jasa_id' => $jasa->id]);
        return redirect()->route('economy.manage', $jasa->id);
    }

    public function logout()
    {
        session()->forget('warga_phone');
        session()->forget('manage_jasa_id');
        return redirect()->route('landing')->with('success', 'Anda telah keluar dari Dasbor Warga.');
    }

    /**
     * Update Operational Hours & Holiday Status (Masyarakat friendly)
     */
    public function updateOperationalStatus(Request $request)
    {
        $request->validate([
            'type' => 'required|in:umkm,jasa,umkm_local',
            'id' => 'required',
            'is_on_holiday' => 'required|boolean',
            'operating_hours' => 'nullable|string|max:50',
        ]);

        $model = null;
        if ($request->type === 'umkm') $model = Umkm::find($request->id);
        if ($request->type === 'jasa') $model = WorkDirectory::find($request->id);
        if ($request->type === 'umkm_local') $model = UmkmLocal::find($request->id);

        if (!$model) {
            return back()->with('error', 'Data tidak ditemukan.');
        }

        // Security check using service normalization
        $phone = session('warga_phone');
        $basePhone = $this->portalService->normalizePhone($phone);
        
        $modelPhoneField = $request->type === 'umkm' ? 'no_wa' : ($request->type === 'jasa' ? 'contact_phone' : 'contact_wa');
        $modelPhone = $this->portalService->normalizePhone($model->$modelPhoneField);

        if (!str_contains($modelPhone, $basePhone)) {
            return back()->with('error', 'Akses ditolak.');
        }

        // Update correct fields
        if ($request->type === 'umkm_local') {
            // Global update for all products with same phone
            UmkmLocal::where('contact_wa', $model->contact_wa)->update([
                'is_on_holiday' => $request->is_on_holiday,
                'operating_hours' => $request->operating_hours ?: $model->operating_hours
            ]);
        } else {
            $model->update([
                'is_on_holiday' => $request->is_on_holiday,
                'operating_hours' => $request->operating_hours ?: $model->operating_hours
            ]);
        }

        $statusLabel = $request->is_on_holiday ? 'diliburkan' : 'diaktifkan kembali';
        return back()->with('success', "Status berhasil diperbarui! Toko/Jasa Anda kini {$statusLabel}.");
    }

    /**
     * Update Business/Service name from Super Dashboard
     */
    public function updateName(Request $request)
    {
        $request->validate([
            'type' => 'required|in:umkm,jasa,umkm_local',
            'id' => 'required',
            'name' => 'required|string|max:255',
        ]);

        $model = null;
        if ($request->type === 'umkm') $model = Umkm::find($request->id);
        if ($request->type === 'jasa') $model = WorkDirectory::find($request->id);
        if ($request->type === 'umkm_local') $model = UmkmLocal::find($request->id);

        if (!$model) {
            return back()->with('error', 'Data tidak ditemukan.');
        }

        // Security check using service normalization
        $phone = session('warga_phone');
        $basePhone = $this->portalService->normalizePhone($phone);
        
        $modelPhoneField = $request->type === 'umkm' ? 'no_wa' : ($request->type === 'jasa' ? 'contact_phone' : 'contact_wa');
        $modelPhone = $this->portalService->normalizePhone($model->$modelPhoneField);

        if (!str_contains($modelPhone, $basePhone)) {
            return back()->with('error', 'Akses ditolak.');
        }

        // 30-day Cooldown Check
        $lastUpdate = $model->name_updated_at;
        if ($lastUpdate && $lastUpdate->diffInDays(now()) < 30) {
            $daysLeft = 30 - $lastUpdate->diffInDays(now());
            return back()->with('error', "Nama toko/jasa hanya bisa diubah setiap 30 hari. Silakan tunggu {$daysLeft} hari lagi.");
        }

        // Update correct field
        if ($request->type === 'umkm_local') {
            // Global update for all products with same phone
            UmkmLocal::where('contact_wa', $model->contact_wa)->update([
                'name' => $request->name,
                'name_updated_at' => now()
            ]);
        } else {
            $fieldName = $request->type === 'umkm' ? 'nama_usaha' : ($request->type === 'jasa' ? 'job_title' : 'name');
            $model->update([
                $fieldName => $request->name,
                'name_updated_at' => now()
            ]);
        }

        return back()->with('success', "Identitas berhasil diubah menjadi '{$request->name}'!");
    }

    // sendWhatsAppMagicLink moved to PortalService as dispatchWhatsAppMagicLink
}
