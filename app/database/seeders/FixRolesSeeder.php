<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixRolesSeeder extends Seeder
{
    public function run()
    {
        // Fix PostgreSQL Sequence if needed
        if (DB::connection()->getDriverName() === 'pgsql') {
            $maxId = DB::table('roles')->max('id') ?: 0;
            DB::statement("SELECT setval('roles_id_seq', $maxId, true)");
        }

        $targetRoles = [
            'Super Admin' => 'Akses penuh ke seluruh sistem, manajemen user, dan konfigurasi master.',
            'Operator Kecamatan' => 'Pengelola data wilayah kecamatan, monitoring desa, dan verifikasi adminstratif.',
            'Operator Desa' => 'Penginput data pembangunan dan administrasi tingkat desa.',
            'Verifikator' => 'Menyetuju atau menolak pengajuan dan submission dari desa.',
            'Auditor' => 'Melihat laporan dan log aktivitas sistem tanpa hak edit (view-only).',
        ];

        $hasNamaRole = Schema::hasColumn('roles', 'nama_role');
        $hasDeskripsi = Schema::hasColumn('roles', 'deskripsi');

        // 1. Create/Update Target Roles
        foreach ($targetRoles as $name => $desc) {
            $data = [
                'guard_name' => 'web'
            ];
            if ($hasNamaRole) $data['nama_role'] = $name;
            if ($hasDeskripsi) $data['deskripsi'] = $desc;

            Role::updateOrCreate(
                ['name' => $name],
                $data
            );
        }

        // 2. Handle known duplicates/mismatches
        $mappings = [
            'super_admin_kabupaten' => 'Super Admin',
            'operator_kecamatan' => 'Operator Kecamatan',
            'operator_desa' => 'Operator Desa',
            'verifikator_kecamatan' => 'Verifikator',
            'admin_kecamatan' => 'Operator Kecamatan', // Map camat to operator kec for now or keep separate?
            'kepala_desa' => 'Operator Desa', // Map kades to operator desa or keep separate?
        ];

        foreach ($mappings as $oldName => $newName) {
            $oldRole = Role::where('name', $oldName)->first();
            $newRole = Role::where('name', $newName)->first();

            if ($oldRole && $newRole && $oldRole->id !== $newRole->id) {
                $this->command->info("Merging role '{$oldName}' into '{$newName}'...");
                
                // Update users role_id in users table (if using direct role_id)
                User::where('role_id', $oldRole->id)->update(['role_id' => $newRole->id]);

                // Update Spatie pivot tables
                DB::table('model_has_roles')->where('role_id', $oldRole->id)->update(['role_id' => $newRole->id]);
                DB::table('role_has_permissions')->where('role_id', $oldRole->id)->update(['role_id' => $newRole->id]);

                // Delete old role
                $oldRole->delete();
            }
        }

        $this->command->info("Roles have been fixed and normalized.");
    }
}
