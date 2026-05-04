<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;
$roles = Role::all();
echo "ID | Name (Spatie) | Nama Role (Col) | Guard\n";
echo "---|---------------|-----------------|------\n";
foreach ($roles as $role) {
    echo $role->id . " | " . $role->name . " | " . ($role->nama_role ?? 'N/A') . " | " . $role->guard_name . "\n";
}
