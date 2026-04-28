<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use App\Traits\Auditable;

class Berita extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'berita';

    protected $fillable = [
        'judul',
        'slug',
        'ringkasan',
        'konten',
        'kategori',
        'thumbnail',
        'status',
        'view_count',
        'author_id',
        'published_at',
        'desa_id',
        'scope',
        'source_type',
        'external_url',
        'external_source',
        'external_id',
        'clickbait_headline',
        'priority_level',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Scope a query to only include published news.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    protected $appends = ['thumbnail_url'];

    /**
     * Get the robust thumbnail URL with fallback support.
     */
    public function getThumbnailUrlAttribute(): string
    {
        $thumb = $this->thumbnail;

        // 1. If empty, return a high-quality placeholder based on category
        if (empty($thumb)) {
            return $this->getFallbackImage();
        }

        // 2. Handle relative paths (legacy or local uploads)
        if (!Str::startsWith($thumb, ['http://', 'https://', 'data:'])) {
            return asset('storage/' . $thumb);
        }

        // 3. Robustness: Check for common placeholder patterns or broken indicators
        $placeholders = ['placeholder', 'loading', 'pixel', 'blank', 'no-image'];
        foreach ($placeholders as $p) {
            if (Str::contains(strtolower($thumb), $p)) {
                return $this->getFallbackImage();
            }
        }

        return $thumb;
    }

    /**
     * Internal helper for beautiful fallback images.
     */
    private function getFallbackImage(): string
    {
        $category = strtolower($this->kategori ?? '');
        
        if (Str::contains($category, 'umkm')) {
            return "https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&q=80&w=800"; // Market/Produce
        }
        
        if (Str::contains($category, 'desa')) {
            return "https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&q=80&w=800"; // Rural/Field
        }

        // Default: Professional Government/Building
        return "https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&q=80&w=800";
    }

    /**
     * Relationship to the author (User).
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Relationship to the village (Desa).
     */
    public function desa()
    {
        return $this->belongsTo(Desa::class, 'desa_id');
    }

    /**
     * Generate slug before saving if not provided.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($berita) {
            if (!$berita->slug) {
                $berita->slug = Str::slug($berita->judul) . '-' . Str::random(5);
            }
        });
    }
}
