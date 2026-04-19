<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DesaOperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get the Operator Desa role
        $role = Role::where('nama_role', 'Operator Desa')->first();

        if (!$role) {
            $this->command->error('Role "Operator Desa" tidak ditemukan. Pastikan RoleSeeder sudah dijalankan.');
            return;
        }

        // 2. Get all villages (should be 17)
        $desas = Desa::all();

        if ($desas->count() === 0) {
            $this->command->error('Data desa tidak ditemukan. Pastikan DesaSeeder sudah dijalankan.');
            return;
        }

        $this->command->info("Memulai seeding operator untuk " . $desas->count() . " desa...");

        foreach ($desas as $desa) {
            // Format username: namadesa_op (lowercase, remove spaces)
            $username = Str::lower(str_replace(' ', '', $desa->nama_desa)) . '_op';

            // Check if village already has an operator OR if the username is taken
            $existingUser = User::where('username', $username)
                ->orWhere(function ($query) use ($desa, $role) {
                    $query->where('desa_id', $desa->id)
                        ->where('role_id', $role->id);
                })
                ->first();

            if (!$existingUser) {
                User::create([
                    'nama_lengkap' => 'Operator ' . $desa->nama_desa,
                    'username' => $username,
                    'password' => Hash::make('operator'),
                    'role_id' => $role->id,
                    'desa_id' => $desa->id,
                    'status' => 'aktif',
                ]);
                $this->command->info("✓ Berhasil membuat operator untuk: {$desa->nama_desa} ({$username})");
            } else {
                $this->command->warn("! Operator untuk {$desa->nama_desa} sudah ada (Username: {$existingUser->username}). Dilewati.");
            }
        }

        $this->command->info("Selesai seeding operator desa.");
    }
}
