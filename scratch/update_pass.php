<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\User::where('role', 'operator_desa')->update(['password' => \Illuminate\Support\Facades\Hash::make('operator')]);
\App\Models\User::where('email', 'admin@probolinggokab.go.id')->update(['password' => \Illuminate\Support\Facades\Hash::make('super123')]);
echo "DONE\n";
