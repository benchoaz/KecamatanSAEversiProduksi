<?php

namespace App\Models\Hub;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HubDistrict extends Model
{
    use HasFactory;

    protected $table = 'hub_districts';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'db_connection_name', // Nama koneksi di config/database.php
        'db_host',
        'db_port',
        'db_name',
        'db_user',
        'db_pass',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'db_pass' => 'encrypted',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public $incrementing = false;

    protected $keyType = 'string';

    public function sessions()
    {
        return $this->hasMany(HubWaSession::class, 'hub_district_id');
    }
}
