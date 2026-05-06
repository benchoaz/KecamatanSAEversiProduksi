<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmkmPromotion extends Model
{
    protected $fillable = [
        'umkm_id',
        'code',
        'type',
        'value',
        'min_purchase',
        'max_discount',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function umkm()
    {
        return $this->belongsTo(Umkm::class);
    }
}
