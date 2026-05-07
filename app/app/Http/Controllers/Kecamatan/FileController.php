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

        $disks = ['local', 'public'];
        $prefixes = ['', 'local/', 'public/', 'app/', 'storage/app/'];

        foreach ($disks as $disk) {
            foreach ($prefixes as $prefix) {
                $testPath = $prefix . $path;
                if (Storage::disk($disk)->exists($testPath)) {
                    $root = ($disk === 'public') ? storage_path('app/public/') : storage_path('app/');
                    return $root . $testPath;
                }
            }
        }

        // Final attempt: check if it's already an absolute path that exists
        if (file_exists($path)) {
            return $path;
        }

        \Illuminate\Support\Facades\Log::warning("File not found in any expected location: " . $path);
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
            // Return default placeholder if photo not found
            $defaultPath = public_path('assets/images/default-user.png');
            if (file_exists($defaultPath)) {
                return response()->file($defaultPath);
            }
            
            // Fallback to a remote placeholder if local one missing
            return redirect('https://ui-avatars.com/api/?name=' . urlencode($personil->nama) . '&background=random&color=fff');
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
