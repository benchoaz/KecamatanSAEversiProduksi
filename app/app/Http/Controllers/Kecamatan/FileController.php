<?php

namespace App\Http\Controllers\Kecamatan;

use App\Http\Controllers\Controller;
use App\Models\PersonilDesa;
use App\Models\LembagaDesa;
use App\Models\DokumenDesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    private function resolvePath($path)
    {
        if (!$path) return null;

        // Try exact path
        if (Storage::disk('local')->exists($path)) {
            return storage_path('app/' . $path);
        }

        // Try with 'local/' prefix (often used in VPS deployments)
        if (Storage::disk('local')->exists('local/' . $path)) {
            return storage_path('app/local/' . $path);
        }

        // Try with 'public/' prefix
        if (Storage::disk('local')->exists('public/' . $path)) {
            return storage_path('app/public/' . $path);
        }

        return null;
    }

    public function personil($id)
    {
        $personil = PersonilDesa::findOrFail($id);
        $fullPath = $this->resolvePath($personil->file_sk);

        if (!$fullPath) {
            abort(404, 'File SK not found.');
        }

        return response()->file($fullPath);
    }

    public function personilFoto($id)
    {
        $personil = PersonilDesa::findOrFail($id);
        $fullPath = $this->resolvePath($personil->foto);

        if (!$fullPath) {
            abort(404, 'Foto not found.');
        }

        return response()->file($fullPath);
    }

    public function lembaga($id)
    {
        $lembaga = LembagaDesa::findOrFail($id);
        $fullPath = $this->resolvePath($lembaga->file_sk);

        if (!$fullPath) {
            abort(404, 'File SK not found.');
        }

        return response()->file($fullPath);
    }

    public function dokumen($id)
    {
        $dokumen = DokumenDesa::findOrFail($id);
        $fullPath = $this->resolvePath($dokumen->file_path);

        if (!$fullPath) {
            abort(404, 'Dokumen not found.');
        }

        return response()->file($fullPath);
    }

    public function perencanaanBa($id)
    {
        $perencanaan = \App\Models\PerencanaanDesa::findOrFail($id);
        $fullPath = $this->resolvePath($perencanaan->file_ba);

        if (!$fullPath) {
            abort(404, 'File BA not found.');
        }

        return response()->file($fullPath);
    }

    public function perencanaanAbsensi($id)
    {
        $perencanaan = \App\Models\PerencanaanDesa::findOrFail($id);
        $fullPath = $this->resolvePath($perencanaan->file_absensi);

        if (!$fullPath) {
            abort(404, 'File Absensi not found.');
        }

        return response()->file($fullPath);
    }

    public function perencanaanFoto($id)
    {
        $perencanaan = \App\Models\PerencanaanDesa::findOrFail($id);
        $fullPath = $this->resolvePath($perencanaan->file_foto);

        if (!$fullPath) {
            abort(404, 'Foto Perencanaan not found.');
        }

        return response()->file($fullPath);
    }
}
