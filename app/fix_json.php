<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AppProfile;

$profile = AppProfile::first();
if ($profile && $profile->google_drive_json) {
    // Memastikan \n literal diubah menjadi karakter newline asli
    $cleanJson = str_replace('\n', "\n", $profile->google_drive_json);
    $profile->google_drive_json = $cleanJson;
    $profile->save();
    echo "JSON Key Berhasil Diperbaiki!\n";
} else {
    echo "Data Profile atau JSON tidak ditemukan.\n";
}
