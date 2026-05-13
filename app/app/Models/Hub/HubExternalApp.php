<?php

namespace App\Models\Hub;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HubExternalApp extends Model
{
    use HasUuids;

    protected $table = 'hub_external_apps';

    protected $fillable = [
        'name',
        'client_id',
        'client_secret',
        'base_url',
        'callback_url',
        'status',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Mendapatkan daftar scope yang diizinkan untuk aplikasi ini.
     */
    public function getScopesAttribute()
    {
        return $this->settings['scopes'] ?? [];
    }
}
