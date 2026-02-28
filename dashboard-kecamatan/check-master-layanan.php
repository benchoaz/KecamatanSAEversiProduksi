<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = App\Models\MasterLayanan::count();
echo "Count: " . $count . "\n";

$all = App\Models\MasterLayanan::all();
foreach ($all as $l) {
    echo $l->slug . " = " . $l->nama_layanan . " (active: " . ($l->is_active ? 'yes' : 'no') . ")\n";
}
