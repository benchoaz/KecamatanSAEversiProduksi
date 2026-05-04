<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WhatsappSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'state',
        'temp_data',
    ];

    protected $casts = [
        'temp_data' => 'array',
        'updated_at' => 'datetime',
    ];

    /**
     * Get or create session for a phone number
     */
    public static function getOrCreate(string $phone): self
    {
        return static::firstOrCreate(
            ['phone' => $phone],
            ['state' => null, 'temp_data' => null]
        );
    }

    /**
     * Update session state
     */
    public function updateState(?string $state, ?array $tempData = null): void
    {
        $this->update([
            'state' => $state,
            'temp_data' => $tempData ?? $this->temp_data,
        ]);
    }

    /**
     * Clear session data
     */
    public function clear(): void
    {
        $this->update([
            'state' => null,
            'temp_data' => null,
        ]);
    }

    /**
     * Check if session is active
     */
    public function isActive(): bool
    {
        return !is_null($this->state);
    }

    /**
     * Check if session is stale (older than 30 seconds for active, 30 mins for idle)
     */
    public function isStale(): bool
    {
        if ($this->isActive()) {
            return $this->updated_at->diffInSeconds(Carbon::now()) > 30;
        }
        return $this->updated_at->diffInMinutes(Carbon::now()) > 30;
    }

    /**
     * Get temporary data value
     */
    public function getTempValue(string $key, $default = null)
    {
        return data_get($this->temp_data, $key, $default);
    }

    /**
     * Set temporary data value
     */
    public function setTempValue(string $key, $value): void
    {
        $tempData = $this->temp_data ?? [];
        data_set($tempData, $key, $value);
        $this->update(['temp_data' => $tempData]);
    }
}
