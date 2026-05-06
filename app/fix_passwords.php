<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$role = \App\Models\Role::where('nama_role', 'Operator Desa')->first();
if ($role) {
    \App\Models\User::where('role_id', $role->id)->update(['password' => \Illuminate\Support\Facades\Hash::make('operator')]);
}
\App\Models\User::where('username', 'admin')->update(['password' => \Illuminate\Support\Facades\Hash::make('super123')]);
echo "DONE REAL FIX\n";
