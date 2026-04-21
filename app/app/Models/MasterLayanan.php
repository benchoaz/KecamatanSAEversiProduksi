<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterLayanan extends Model
{
    use HasFactory;

    protected $table = 'master_layanan';

    protected $fillable = [
        'nama_layanan',
        'slug',
        'deskripsi_syarat',
        'attachment_requirements',
        'estimasi_waktu',
        'ikon',
        'warna_bg',
        'warna_text',
        'is_active',
        'urutan',
        'is_popular',
        'link_type',
        'custom_link',
        'has_nodes',
    ];

    public function serviceNodes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ServiceNode::class, 'master_layanan_id')
            ->whereNull('parent_id')
            ->orderBy('urutan');
    }

    protected $casts = [
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'urutan' => 'integer',
        'attachment_requirements' => 'array'
    ];

    /**
     * Boot function for model events.
     */
    protected static function booted()
    {
        static::saving(function ($layanan) {
            if (empty($layanan->slug) && !empty($layanan->nama_layanan)) {
                $layanan->slug = \Illuminate\Support\Str::slug($layanan->nama_layanan);
            }
        });
    }
}
