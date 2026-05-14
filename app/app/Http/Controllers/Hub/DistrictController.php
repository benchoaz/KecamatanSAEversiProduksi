<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Models\Hub\HubDistrict;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    /**
     * Tampilkan daftar seluruh kecamatan di kabupaten.
     */
    public function index()
    {
        $districts = HubDistrict::orderBy('name')->get();

        return view('hub.districts.index', compact('districts'));
    }

    /**
     * Simpan data kecamatan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:districts,slug',
            'db_name' => 'required|string',
        ]);

        HubDistrict::create($validated);

        return redirect()->back()->with('success', 'Kecamatan berhasil ditambahkan ke Gateway!');
    }

    /**
     * Aktifkan atau Nonaktifkan kecamatan.
     */
    public function toggleStatus(HubDistrict $district)
    {
        $district->update(['is_active' => ! $district->is_active]);

        return redirect()->back()->with('success', 'Status kecamatan berhasil diubah!');
    }
}
