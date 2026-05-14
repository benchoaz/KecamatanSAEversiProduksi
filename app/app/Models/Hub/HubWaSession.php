<?php

namespace App\Models\Hub;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HubWaSession extends Model
{
    use HasFactory;

    protected $table = 'hub_wa_sessions';

    protected $fillable = [
        'phone_number',
        'hub_district_id',
        'last_interaction_at',
        'context_data',
        'is_active',
    ];

    protected $casts = [
        'last_interaction_at' => 'datetime',
        'context_data' => 'array',
        'is_active' => 'boolean',
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

    public function district()
    {
        return $this->belongsTo(HubDistrict::class, 'hub_district_id');
    }
}
