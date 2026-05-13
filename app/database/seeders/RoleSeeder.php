<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'deskripsi' => 'Akses penuh ke seluruh sistem, manajemen user, dan konfigurasi master.'
            ],
            [
                'name' => 'super_admin_kabupaten',
                'deskripsi' => 'Pengendali pusat seluruh kecamatan dalam satu kabupaten.'
            ],
            [
                'name' => 'Operator Kecamatan',
                'deskripsi' => 'Pengelola data wilayah kecamatan, monitoring desa, dan verifikasi adminstratif.'
            ],
            [
                'name' => 'Operator Desa',
                'deskripsi' => 'Penginput data pembangunan dan administrasi tingkat desa.'
            ],
            [
                'name' => 'Verifikator',
                'deskripsi' => 'Menyetuju atau menolak pengajuan dan submission dari desa.'
            ],
            [
                'name' => 'Auditor',
                'deskripsi' => 'Melihat laporan dan log aktivitas sistem tanpa hak edit (view-only).'
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                ['guard_name' => 'web']
            );
        }
    }
}
