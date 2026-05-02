<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FixNavigationPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Define all permissions from NavigationSeeder
        $navPermissions = [
            'view_dashboard', 'view_inbox', 'view_inbox_pelayanan', 'view_inbox_ekonomi',
            'view_pengaduan', 'view_visitor', 'view_faq', 'view_layanan',
            'view_statistics', 'view_announcements', 'view_pemerintahan',
            'view_pemerintahan_index', 'view_pembangunan', 'view_pembangunan_index',
            'view_ssh', 'view_sbu', 'view_pencairan_dd', 'view_kesra',
            'view_trantibum', 'view_trantibum_index', 'view_trantibum_kejadian',
            'view_trantibum_relawan', 'view_laporan', 'view_berita', 'view_umkm',
            'view_users', 'view_master_desa', 'view_geospasial', 'view_settings_profile',
            'view_settings_features', 'view_settings_bot', 'view_audit_logs',
            'view_desa_dashboard', 'view_desa_administrasi', 'view_desa_perencanaan_group',
            'view_desa_musdes', 'view_desa_perencanaan', 'view_desa_pembangunan_group',
            'view_desa_pagu', 'view_desa_fisik', 'view_desa_non_fisik', 'view_desa_blt',
            'view_desa_pencairan', 'view_desa_adm_kegiatan', 'view_desa_trantibum_group',
            'view_desa_trantibum', 'view_desa_relawan', 'view_desa_submissions'
        ];

        // Ensure permissions exist
        foreach ($navPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Assign to Kecamatan Roles
        $kecamatanRoles = ['admin_kecamatan', 'verifikator_kecamatan', 'super_admin_kabupaten'];
        foreach ($kecamatanRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($navPermissions);
            }
        }
        
        // Assign Desa Permissions to Desa Roles
        $desaPermissions = array_filter($navPermissions, function($p) {
            return str_starts_with($p, 'view_desa');
        });
        
        $desaRoles = ['operator_desa', 'kepala_desa'];
        foreach ($desaRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($desaPermissions);
            }
        }

        echo "✅ Permissions fixed and assigned to roles.\n";
    }
}
