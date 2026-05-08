<?php

require 'vendor/autoload.php';

use App\Models\AppProfile;
use Google\Client;
use Google\Service\Drive;
use Masbug\Flysystem\GoogleDriveAdapter;
use League\Flysystem\Filesystem;

// Mock Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- GOOGLE DRIVE DIAGNOSTIC ---\n";

try {
    $profile = AppProfile::first();
    $jsonContent = $profile->google_drive_json;
    $folderId = $profile->google_drive_folder_id;

    $authData = json_decode($jsonContent, true);
    $client = new Client();
    $client->setAuthConfig($authData);
    $client->addScope(Drive::DRIVE);
    $service = new Drive($client);

    echo "\n1. Listing Accessible Shared Drives (Team Drives):\n";
    try {
        $drives = $service->teamdrives->listTeamdrives(['pageSize' => 10])->getTeamDrives();
        if (empty($drives)) {
            echo "   - NO Shared Drives found! (Service account might not be added to the Shared Drive itself)\n";
        } else {
            foreach ($drives as $td) {
                echo "   - Name: " . $td->getName() . " | ID: " . $td->getId() . "\n";
            }
        }
    } catch (\Exception $e) {
        echo "   - Error listing Shared Drives: " . $e->getMessage() . "\n";
    }

    echo "\n2. Testing Folder Access (ID: $folderId)...\n";
    try {
        $folder = $service->files->get($folderId, [
            'fields' => 'id, name, mimeType, driveId',
            'supportsAllDrives' => true
        ]);
        echo "   - Folder Found: " . $folder->name . " (" . $folder->mimeType . ")\n";
        echo "   - Belongs to Drive ID: " . ($folder->driveId ?: "None (My Drive)") . "\n";
        
        $targetDriveId = $folder->driveId;

        echo "\n3. Testing Write Permission with detected Drive ID...\n";
        $options = [
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ];
        if ($targetDriveId) {
            $options['teamDriveId'] = $targetDriveId;
        }

        $adapter = new GoogleDriveAdapter($service, $folderId, $options);
        $filesystem = new Filesystem($adapter);

        $testFileName = 'test_backup_' . time() . '.txt';
        $filesystem->write($testFileName, 'KecamatanSAE Backup Connection Test at ' . date('Y-m-d H:i:s'));
        echo "   - Write Success! Created file: $testFileName\n";

    } catch (\Exception $e) {
        echo "   - ERROR: " . $e->getMessage() . "\n";
    }

    echo "\n--- DIAGNOSTIC COMPLETED ---\n";

} catch (\Exception $e) {
    echo "\nFATAL ERROR: " . $e->getMessage() . "\n";
}
