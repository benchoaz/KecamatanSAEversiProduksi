<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminCredentialsSeeder extends Seeder
{
    public function run(): void
    {
        $username = 'admin';
        $password = 'admin123';

        // Find Super Admin role ID safely
        $roleId = DB::table('roles')->where('name', 'Super Admin')->value('id');
        
        if (!$roleId) {
            $roleId = DB::table('roles')->where('nama_role', 'Super Admin')->value('id');
        }

        if (!$roleId) {
            // Create it if missing
            $column = \Illuminate\Support\Facades\Schema::hasColumn('roles', 'nama_role') ? 'nama_role' : 'name';
            $roleId = DB::table('roles')->insertGetId([
                $column => 'Super Admin',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        User::updateOrCreate(
            ['username' => $username],
            [
                'password' => Hash::make($password),
                'role_id' => $roleId,
                'status' => 'aktif',
                'nama_lengkap' => 'Administrator Pusat'
            ]
        );

        echo "✅ Admin credentials updated.\n";
    }
}
