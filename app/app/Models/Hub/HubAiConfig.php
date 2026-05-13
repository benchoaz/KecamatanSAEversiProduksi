<?php

namespace App\Models\Hub;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class HubAiConfig extends Model
{
    use HasFactory;

    protected $table = 'hub_ai_configs';

    protected $fillable = [
        'key',
        'value',
        'description',
        'is_global'
    ];

    protected $casts = [
        'is_global' => 'boolean',
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
}
