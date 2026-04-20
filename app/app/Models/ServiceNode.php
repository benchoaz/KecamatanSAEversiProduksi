<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceNode extends Model
{
    protected $fillable = [
        'master_layanan_id',
        'parent_id',
        'depth',
        'name',
        'description',
        'ikon',
        'urutan',
        'is_leaf',
        'is_active',
    ];

    protected $casts = [
        'is_leaf'   => 'boolean',
        'is_active' => 'boolean',
        'depth'     => 'integer',
        'urutan'    => 'integer',
    ];

    // ─── Relasi ──────────────────────────────────────────

    public function masterLayanan(): BelongsTo
    {
        return $this->belongsTo(MasterLayanan::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ServiceNode::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ServiceNode::class, 'parent_id')
                    ->where('is_active', true)
                    ->orderBy('urutan');
    }

    public function allChildren(): HasMany
    {
        return $this->hasMany(ServiceNode::class, 'parent_id')
                    ->with('allChildren')
                    ->orderBy('urutan');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(ServiceRequirement::class, 'node_id')
                    ->orderBy('urutan');
    }

    // ─── Scopes ──────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLeaves($query)
    {
        return $query->where('is_leaf', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    // ─── Helpers ─────────────────────────────────────────

    /**
     * Ambil breadcrumb dari root ke node ini.
     * Return: Collection of ServiceNode
     */
    public function getBreadcrumb(): \Illuminate\Support\Collection
    {
        $breadcrumb = collect();
        $node = $this;

        while ($node) {
            $breadcrumb->prepend($node);
            $node = $node->parent_id ? ServiceNode::find($node->parent_id) : null;
        }

        return $breadcrumb;
    }

    /**
     * Cek apakah node ini bisa menampilkan form (is_leaf = true
     * dan punya requirements yang dikonfigurasi).
     */
    public function isReadyForSubmission(): bool
    {
        return $this->is_leaf && $this->requirements()->exists();
    }
}
