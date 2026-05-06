<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$sa = User::where('username', 'superadmin')->first();
if ($sa) {
    $sa->password = bcrypt('super123');
    $sa->save();
    echo "Superadmin reset successful\n";
}

$ops = User::where('username', 'like', '%operator%')->get();
foreach ($ops as $op) {
    $op->password = bcrypt('operator');
    $op->save();
    echo "Operator {$op->username} reset successful\n";
}
