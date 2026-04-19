<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Loker extends Model
{
    use HasFactory;

    protected $table = 'lokers';

    const STATUS_AKTIF = 'aktif';
    const STATUS_NONAKTIF = 'nonaktif';
    const STATUS_WAITING = 'menunggu_verifikasi';

    protected $fillable = [
        'uuid',
        'title',
        'job_category',
        'desa_id',
        'nama_desa_manual',
        'contact_wa',
        'description',
        'work_time',
        'is_available_today',
        'status',
        'is_sensitive',
        'manage_token',
        'source',
        'internal_notes',
        'is_verified',
        'is_flagged',
        'owner_pin',
        'last_toggle_at',
    ];

    protected $casts = [
        'is_available_today' => 'boolean',
        'is_sensitive' => 'boolean',
        'is_verified' => 'boolean',
        'is_flagged' => 'boolean',
        'last_toggle_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
            if (!$model->manage_token) {
                $model->manage_token = Str::random(40);
            }
        });
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'desa_id');
    }
}
