<?php

use App\Models\User;
use App\Models\Role as LegacyRole;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$username = 'admin';
$password = 'admin123';

// 1. Get or Create Super Admin Role (Spatie/Legacy shared)
$legacyRole = SpatieRole::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

// 2. Create or Update User
$user = User::updateOrCreate(
    ['username' => $username],
    [
        'password' => Hash::make($password),
        'role_id' => $legacyRole->id,
        'status' => 'aktif',
        'nama_lengkap' => 'Administrator Pusat'
    ]
);

// 3. Assign Spatie Role for Permissions
$spatieRole = SpatieRole::firstOrCreate(['name' => 'super_admin_kabupaten', 'guard_name' => 'web']);
if (!$user->hasRole('super_admin_kabupaten')) {
    $user->assignRole($spatieRole);
}

echo "✅ User '{$username}' updated successfully with password '{$password}'\n";
