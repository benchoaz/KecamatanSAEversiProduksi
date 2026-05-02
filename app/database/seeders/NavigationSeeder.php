<?php

namespace Database\Seeders;

use App\Models\NavMenu;
use App\Models\NavSubMenu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NavigationSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data to avoid duplicates/confusion
        NavSubMenu::truncate();
        NavMenu::truncate();

        $this->seedKecamatanMenus();
        $this->seedDesaMenus();
        $this->assignPermissionsToRoles();
    }

    private function seedKecamatanMenus()
    {
        // 1. DASHBOARD PUSAT
        $this->createMenu('Beranda Pusat', 'fas fa-layer-group', 'kecamatan-dashboard', 10, 'view_dashboard', 'kecamatan', 'kecamatan.dashboard');

        // 2. INBOX TERPADU (WITH SUBMENUS)
        $mInbox = $this->createMenu('Inbox Terpadu', 'fas fa-inbox', 'kecamatan-inbox', 20, 'view_inbox', 'kecamatan', 'kecamatan.pelayanan.inbox');
        $this->createSubMenu($mInbox->id, 'Pelayanan Berkas', 'kecamatan.pelayanan.inbox', 'inbox-pelayanan', 1, 'view_inbox_pelayanan');
        $this->createSubMenu($mInbox->id, 'Pendaftaran Usaha & Jasa', 'kecamatan.pelayanan.inbox', 'inbox-ekonomi', 2, 'view_inbox_ekonomi');

        // 3. CORE SERVICES
        $this->createMenu('Pengaduan Masyarakat', 'fas fa-bullhorn', 'kecamatan-pengaduan', 30, 'view_pengaduan', 'kecamatan', 'kecamatan.pelayanan.pengaduan');
        $this->createMenu('Buku Tamu', 'fas fa-clipboard-user', 'kecamatan-visitor', 40, 'view_visitor', 'kecamatan', 'kecamatan.pelayanan.visitor.index');
        $this->createMenu('FAQ Administrasi', 'fas fa-robot', 'kecamatan-faq', 50, 'view_faq', 'kecamatan', 'kecamatan.pelayanan.faq.index');
        $this->createMenu('Daftar Layanan', 'fas fa-layer-group', 'kecamatan-layanan', 60, 'view_layanan', 'kecamatan', 'kecamatan.pelayanan.layanan.index');
        $this->createMenu('Statistik Layanan', 'fas fa-chart-line', 'kecamatan-statistics', 70, 'view_statistics', 'kecamatan', 'kecamatan.pelayanan.statistics');
        $this->createMenu('Pengumuman', 'fas fa-bullhorn', 'kecamatan-announcements', 80, 'view_announcements', 'kecamatan', 'kecamatan.announcements.index');

        // 4. BIDANG PENGAWASAN (WITH SUBMENUS)
        $mPem = $this->createMenu('Pemerintahan', 'fas fa-shield-halved', 'kecamatan-pemerintahan', 90, 'view_pemerintahan', 'kecamatan', 'kecamatan.pemerintahan.index');
        $this->createSubMenu($mPem->id, 'Monev Tata Kelola', 'kecamatan.pemerintahan.index', 'pemerintahan-index', 1, 'view_pemerintahan_index');

        $mEkbang = $this->createMenu('Ekonomi & Pembangunan', 'fas fa-chart-pie', 'kecamatan-pembangunan', 100, 'view_pembangunan', 'kecamatan', 'kecamatan.pembangunan.index');
        $this->createSubMenu($mEkbang->id, 'Monitoring Utama', 'kecamatan.pembangunan.index', 'pembangunan-index', 1, 'view_pembangunan_index');
        $this->createSubMenu($mEkbang->id, 'Master SSH', 'kecamatan.pembangunan.referensi.ssh.index', 'pembangunan-ssh', 2, 'view_ssh');
        $this->createSubMenu($mEkbang->id, 'Master SBU', 'kecamatan.pembangunan.referensi.sbu.index', 'pembangunan-sbu', 3, 'view_sbu');
        $this->createSubMenu($mEkbang->id, 'Validasi Rekomendasi DD', 'kecamatan.pembangunan.index', 'pembangunan-dd', 4, 'view_pencairan_dd');

        $this->createMenu('Kesejahteraan Sosial', 'fas fa-dove', 'kecamatan-kesra', 110, 'view_kesra', 'kecamatan', 'kecamatan.kesra.index');

        $mTrantib = $this->createMenu('Trantibum & Linmas', 'fas fa-masks-theater', 'kecamatan-trantibum', 120, 'view_trantibum', 'kecamatan', 'kecamatan.trantibum.index');
        $this->createSubMenu($mTrantib->id, 'Dashboard Monitoring', 'kecamatan.trantibum.index', 'trantibum-index', 1, 'view_trantibum_index');
        $this->createSubMenu($mTrantib->id, 'Data Laporan', 'kecamatan.trantibum.kejadian', 'trantibum-kejadian', 2, 'view_trantibum_kejadian');
        $this->createSubMenu($mTrantib->id, 'Relawan Tangguh', 'kecamatan.trantibum.relawan', 'trantibum-relawan', 3, 'view_trantibum_relawan');

        $this->createMenu('Laporan Terpadu', 'fas fa-file-invoice', 'kecamatan-laporan', 130, 'view_laporan', 'kecamatan', 'kecamatan.laporan.index');

        // 5. PUBLIKASI
        $this->createMenu('Berita & Artikel', 'fas fa-newspaper', 'kecamatan-berita', 140, 'view_berita', 'kecamatan', 'kecamatan.berita.index');
        $this->createMenu('Etalase Usaha & Jasa', 'fas fa-store', 'kecamatan-umkm', 150, 'view_umkm', 'kecamatan', 'kecamatan.umkm.index');

        // 6. KONFIGURASI
        $this->createMenu('Manajemen Pengguna', 'fas fa-user-gear', 'kecamatan-users', 160, 'view_users', 'kecamatan', 'kecamatan.users.index');
        $this->createMenu('Data Master Desa', 'fas fa-map-location-dot', 'kecamatan-master-desa', 170, 'view_master_desa', 'kecamatan', 'kecamatan.master.desa.index');
        $this->createMenu('Geospasial Wilayah', 'fas fa-map-location-dot', 'kecamatan-geospasial', 180, 'view_geospasial', 'kecamatan', 'kecamatan.settings.geospasial');
        $this->createMenu('Pengaturan Aplikasi', 'fas fa-sliders', 'kecamatan-settings', 190, 'view_settings_profile', 'kecamatan', 'kecamatan.settings.profile');
        $this->createMenu('Manajemen Fitur', 'fas fa-toggle-on', 'kecamatan-features', 200, 'view_settings_features', 'kecamatan', 'kecamatan.settings.features');
        $this->createMenu('BOT Manajemen', 'fab fa-whatsapp', 'kecamatan-bot', 210, 'view_settings_bot', 'kecamatan', 'kecamatan.settings.waha-n8n.index');
        $this->createMenu('Audit Aktivitas', 'fas fa-file-invoice', 'kecamatan-audit', 220, 'view_audit_logs', 'kecamatan', 'kecamatan.audit-logs.index');
    }

    private function seedDesaMenus()
    {
        $this->createMenu('Dashboard Desa', 'fas fa-home', 'desa-dashboard', 10, 'view_desa_dashboard', 'desa', 'desa.dashboard');
        $this->createMenu('Administrasi Desa', 'fas fa-folder-open', 'desa-administrasi', 20, 'view_desa_administrasi', 'desa', 'desa.pemerintahan.aparatur.index');
        
        $mPlanning = $this->createMenu('Perencanaan', 'fas fa-gavel', 'desa-perencanaan-group', 30, 'view_desa_perencanaan_group', 'desa', 'desa.musdes.index');
        $this->createSubMenu($mPlanning->id, 'Musyawarah Desa', 'desa.musdes.index', 'desa-musdes', 1, 'view_desa_musdes');
        $this->createSubMenu($mPlanning->id, 'Dokumen Perencanaan', 'desa.pemerintahan.detail.perencanaan.index', 'desa-perencanaan', 2, 'view_desa_perencanaan');

        $mBuild = $this->createMenu('Pembangunan & BLT', 'fas fa-trowel-bricks', 'desa-pembangunan-group', 40, 'view_desa_pembangunan_group', 'desa', 'desa.pembangunan.pagu.index');
        $this->createSubMenu($mBuild->id, 'Anggaran Desa', 'desa.pembangunan.pagu.index', 'desa-pagu', 1, 'view_desa_pagu');
        $this->createSubMenu($mBuild->id, 'Pembangunan Fisik', 'desa.pembangunan.fisik.index', 'desa-fisik', 2, 'view_desa_fisik');
        $this->createSubMenu($mBuild->id, 'Kegiatan Non-Fisik', 'desa.pembangunan.non-fisik.index', 'desa-nonfisik', 3, 'view_desa_non_fisik');
        $this->createSubMenu($mBuild->id, 'BLT Desa', 'desa.blt.index', 'desa-blt', 4, 'view_desa_blt');
        $this->createSubMenu($mBuild->id, 'Syarat Pencairan DD', 'desa.dashboard', 'desa-pencairan', 5, 'view_desa_pencairan');
        $this->createSubMenu($mBuild->id, 'Bantuan Administrasi Kegiatan', 'desa.pembangunan.administrasi.index', 'desa-adm-kegiatan', 6, 'view_desa_adm_kegiatan');

        $mDesaTrantib = $this->createMenu('Trantibum Desa', 'fas fa-shield-alt', 'desa-trantibum-group', 50, 'view_desa_trantibum_group', 'desa', 'desa.trantibum.kejadian.index');
        $this->createSubMenu($mDesaTrantib->id, 'Laporan Trantibum', 'desa.trantibum.kejadian.index', 'desa-trantibum', 1, 'view_desa_trantibum');
        $this->createSubMenu($mDesaTrantib->id, 'Tim Relawan', 'desa.trantibum.relawan.index', 'desa-relawan', 2, 'view_desa_relawan');

        $this->createMenu('Laporan & Arsip', 'fas fa-file-alt', 'desa-submissions', 60, 'view_desa_submissions', 'desa', 'desa.submissions.index');
    }

    private function createMenu($name, $icon, $slug, $order, $permission, $target, $route = null)
    {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        
        return NavMenu::create([
            'name' => $name,
            'icon' => $icon,
            'slug' => $slug,
            'route_name' => $route,
            'order' => $order,
            'permission_name' => $permission,
            'is_active' => true,
            'target_dashboard' => $target
        ]);
    }

    private function createSubMenu($menuId, $name, $route, $slug, $order, $permission)
    {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);

        return NavSubMenu::create([
            'menu_id' => $menuId,
            'name' => $name,
            'slug' => $slug,
            'route_name' => $route,
            'order' => $order,
            'permission_name' => $permission,
            'is_active' => true
        ]);
    }

    private function assignPermissionsToRoles()
    {
        $allPermissions = Permission::all()->pluck('name')->toArray();
        
        // Roles to get all kecamatan permissions
        $kecamatanRoles = ['admin_kecamatan', 'verifikator_kecamatan', 'super_admin_kabupaten'];
        foreach ($kecamatanRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                // Filter permissions that start with 'view_' or other relevant prefixes
                $role->givePermissionTo($allPermissions);
            }
        }

        // Roles to get desa permissions
        $desaRoles = ['operator_desa', 'kepala_desa'];
        foreach ($desaRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $desaPerms = array_filter($allPermissions, function($p) {
                    return str_starts_with($p, 'view_desa');
                });
                $role->givePermissionTo($desaPerms);
            }
        }
    }
}
