<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\User::whereNotIn('username', ['admin', 'superadmin'])
    ->update(['password' => \Illuminate\Support\Facades\Hash::make('operator')]);
    
\App\Models\User::where('username', 'admin')
    ->update(['password' => \Illuminate\Support\Facades\Hash::make('super123')]);

echo "ALL PASSWORDS RESET PROPERLY!\n";
