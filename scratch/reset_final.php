<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$sa = \App\Models\User::where('username', 'superadmin')->first();
if ($sa) {
    $sa->password = bcrypt('super123');
    $sa->save();
    echo "SUCCESS: superadmin reset to super123\n";
} else {
    echo "ERROR: superadmin not found\n";
}

$ops = \App\Models\User::where('username', 'like', '%operator%')->get();
foreach ($ops as $op) {
    $op->password = bcrypt('operator');
    $op->save();
    echo "SUCCESS: {$op->username} reset to operator\n";
}
