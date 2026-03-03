<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenDesa extends Model
{
    use HasFactory;

    protected $table = 'dokumen_desa';
    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_penyampaian' => 'date',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\DesaScope);
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class);
    }

    public function isEditable()
    {
        return in_array($this->status, ['draft', 'dikembalikan']);
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'dikirim' => 'Verifikasi Kecamatan',
            'dikembalikan' => 'Perlu Revisi',
            'diterima' => 'Terverifikasi',
            default => 'Draft'
        };
    }

    public function getTipeLabelAttribute()
    {
        return match ($this->tipe_dokumen) {
            'Perdes' => 'Peraturan Desa',
            'Perkades' => 'Peraturan Kades',
            'SK_Desa' => 'SK Kepala Desa',
            'LPPD' => 'LPPD Tahunan',
            'LPPD_AMJ' => 'LPPD-AMJ',
            'LKPPD' => 'LKPPD (BPD)',
            'LPJ_APBDes' => 'LPJ APBDesa',
            'IPPD' => 'I.P.P.D (Masyarakat)',
            'BUMDes' => 'Laporan BUMDes',
            'Rekap_Penduduk' => 'Rekap Penduduk',
            default => $this->tipe_dokumen
        };
    }
}
