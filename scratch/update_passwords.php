<?php
use Illuminate\Support\Facades\Hash;
use App\Models\User;

try {
    User::where('role', 'operator_desa')->update(['password' => Hash::make('operator')]);
    User::where('email', 'admin@probolinggokab.go.id')->update(['password' => Hash::make('super123')]);
    echo "Passwords updated successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
