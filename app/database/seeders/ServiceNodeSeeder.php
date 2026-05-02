<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterLayanan;
use App\Models\ServiceNode;

class ServiceNodeSeeder extends Seeder
{
    public function run(): void
    {
        // KTP Nodes
        $ktp = MasterLayanan::where('slug', 'ktp')->first();
        if ($ktp) {
            $root = ServiceNode::create([
                'master_layanan_id' => $ktp->id,
                'name' => 'KTP Baru / Perubahan Data',
                'ikon' => 'fa-plus-circle',
                'urutan' => 1,
                'is_active' => true
            ]);

            ServiceNode::create([
                'master_layanan_id' => $ktp->id,
                'parent_id' => $root->id,
                'name' => 'KTP Baru (Pemula)',
                'ikon' => 'fa-user-plus',
                'is_leaf' => true,
                'requirement_text' => 'Bagi warga yang baru berusia 17 tahun atau belum pernah memiliki KTP.',
                'urutan' => 1,
                'is_active' => true
            ]);

            ServiceNode::create([
                'master_layanan_id' => $ktp->id,
                'parent_id' => $root->id,
                'name' => 'Perubahan Data / Rusak',
                'ikon' => 'fa-edit',
                'is_leaf' => true,
                'requirement_text' => 'Bagi warga yang ingin mengubah data KTP atau KTP dalam kondisi rusak.',
                'urutan' => 2,
                'is_active' => true
            ]);

            ServiceNode::create([
                'master_layanan_id' => $ktp->id,
                'name' => 'KTP Hilang',
                'ikon' => 'fa-search-minus',
                'is_leaf' => true,
                'requirement_text' => 'Wajib melampirkan Surat Keterangan Hilang dari Kepolisian.',
                'urutan' => 2,
                'is_active' => true
            ]);
        }

        // KK Nodes
        $kk = MasterLayanan::where('slug', 'kk')->first();
        if ($kk) {
            $tambah = ServiceNode::create([
                'master_layanan_id' => $kk->id,
                'name' => 'Tambah Anggota Keluarga',
                'ikon' => 'fa-user-friends',
                'urutan' => 1,
                'is_active' => true
            ]);

            ServiceNode::create([
                'master_layanan_id' => $kk->id,
                'parent_id' => $tambah->id,
                'name' => 'Kelahiran Anak',
                'ikon' => 'fa-baby',
                'is_leaf' => true,
                'requirement_text' => 'Melampirkan Akta Kelahiran dan KK Asli.',
                'urutan' => 1,
                'is_active' => true
            ]);

            $pecah = ServiceNode::create([
                'master_layanan_id' => $kk->id,
                'name' => 'Pecah KK / KK Baru',
                'ikon' => 'fa-columns',
                'urutan' => 2,
                'is_active' => true
            ]);

            ServiceNode::create([
                'master_layanan_id' => $kk->id,
                'parent_id' => $pecah->id,
                'name' => 'Pernikahan Baru',
                'ikon' => 'fa-ring',
                'is_leaf' => true,
                'requirement_text' => 'Bagi pasangan yang baru menikah dan ingin membuat KK mandiri.',
                'urutan' => 1,
                'is_active' => true
            ]);
        }
    }
}
