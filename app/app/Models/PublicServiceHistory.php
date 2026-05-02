<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicServiceHistory extends Model
{
    protected $table = 'public_service_history';
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(PublicService::class, 'public_service_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusToLabelAttribute()
    {
        return match ($this->status_to) {
            PublicService::STATUS_MENUNGGU => 'Menunggu Verifikasi',
            PublicService::STATUS_DIPROSES => 'Sedang Diproses',
            PublicService::STATUS_SELESAI => 'Selesai',
            PublicService::STATUS_DITOLAK => 'Ditolak / Tidak Valid',
            default => $this->status_to
        };
    }
}
