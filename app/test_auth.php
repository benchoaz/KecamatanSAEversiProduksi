<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AppProfile;
use Google\Client;
use Google\Service\Drive;

$profile = AppProfile::first();
$authData = json_decode($profile->google_drive_json, true);

echo "Type: " . ($authData['type'] ?? 'N/A') . "\n";

$client = new Client();
$client->setAuthConfig($authData);
$client->addScope(Drive::DRIVE);

try {
    $token = $client->fetchAccessTokenWithAssertion();
    if (isset($token['access_token'])) {
        echo "Token Berhasil Diambil!\n";
        
        $service = new Drive($client);
        $files = $service->files->listFiles(['pageSize' => 1]);
        echo "Koneksi API Berhasil! Jumlah file ditemukan: " . count($files->getFiles()) . "\n";
    } else {
        echo "Gagal mengambil token.\n";
        print_r($token);
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
