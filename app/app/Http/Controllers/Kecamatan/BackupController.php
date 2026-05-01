<?php

namespace App\Http\Controllers\Kecamatan;

use App\Http\Controllers\Controller;
use App\Models\ModuleSetting;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function index()
    {
        $gdrivePath = ModuleSetting::getValue('backup', 'gdrive_path', 'gdrive:backup/kecamatan-files/');
        return view('kecamatan.settings.backup', compact('gdrivePath'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'gdrive_path' => 'required|string|max:255',
        ]);

        ModuleSetting::setValue('backup', 'gdrive_path', $request->gdrive_path, 'string', 'Lokasi folder Google Drive untuk backup cronjob (Rclone)');

        return redirect()->back()->with('success', 'Pengaturan backup berhasil disimpan.');
    }
}
