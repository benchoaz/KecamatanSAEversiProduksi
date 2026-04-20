<?php

namespace App\Services;

use App\Models\MasterLayanan;
use App\Models\ServiceNode;
use App\Models\ServiceRequirement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ServiceTreeService
{
    /**
     * Ambil pohon lengkap untuk satu master_layanan
     */
    public function getTree(int $layananId): Collection
    {
        return Cache::remember("service_tree_{$layananId}", 300, function () use ($layananId) {
            return ServiceNode::where('master_layanan_id', $layananId)
                ->whereNull('parent_id')
                ->with('allChildren.requirements')
                ->active()
                ->orderBy('urutan')
                ->get();
        });
    }

    /**
     * Ambil child nodes langsung di bawah satu parent
     */
    public function getChildren(int $parentId): Collection
    {
        return ServiceNode::where('parent_id', $parentId)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();
    }

    /**
     * Ambil requirements untuk node leaf
     */
    public function getRequirements(int $nodeId): Collection
    {
        return ServiceRequirement::where('node_id', $nodeId)
            ->orderBy('urutan')
            ->get();
    }

    /**
     * Bangun breadcrumb array dari node leaf ke root
     */
    public function getBreadcrumb(int $nodeId): array
    {
        $crumbs = [];
        $node = ServiceNode::find($nodeId);

        while ($node) {
            array_unshift($crumbs, ['id' => $node->id, 'name' => $node->name]);
            $node = $node->parent_id ? ServiceNode::find($node->parent_id) : null;
        }

        return $crumbs;
    }

    /**
     * Bersihkan cache setelah perubahan data
     */
    public function clearCache(int $layananId): void
    {
        Cache::forget("service_tree_{$layananId}");
    }
}
