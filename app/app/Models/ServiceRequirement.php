<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequirement extends Model
{
    protected $fillable = [
        'node_id',
        'type',
        'label',
        'description',
        'is_required',
        'accepted_types',
        'max_size_mb',
        'urutan',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'max_size_mb' => 'integer',
        'urutan'      => 'integer',
    ];

    // Tipe yang didukung
    public const TYPE_FILE   = 'file_upload';
    public const TYPE_INFO   = 'text_info';
    public const TYPE_CHECK  = 'checkbox';

    public static function types(): array
    {
        return [
            self::TYPE_FILE  => 'Upload File',
            self::TYPE_INFO  => 'Informasi Teks',
            self::TYPE_CHECK => 'Pernyataan (Checkbox)',
        ];
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(ServiceNode::class, 'node_id');
    }

    /**
     * Array format file yang diterima, untuk validasi.
     */
    public function getAcceptedTypesArrayAttribute(): array
    {
        return array_map('trim', explode(',', $this->accepted_types ?? 'jpg,png,pdf'));
    }

    /**
     * String untuk atribut `accept` di input file HTML.
     */
    public function getFileAcceptAttribute(): string
    {
        return collect($this->accepted_types_array)
            ->map(fn($ext) => ".$ext")
            ->implode(',');
    }
}
