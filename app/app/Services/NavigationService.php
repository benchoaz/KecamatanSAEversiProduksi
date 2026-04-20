<?php

namespace App\Services;

use App\Models\NavMenu;
use App\Models\NavSubMenu;
use Illuminate\Support\Facades\Cache;

class NavigationService
{
    /**
     * Get allowed navigation menus for the current user and dashboard.
     *
     * @param string $dashboard 'kecamatan' or 'desa'
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMenus(string $dashboard)
    {
        $user = auth()->user();
        if (!$user) return collect();

        // Use caching to optimize performance for thousands of users
        $cacheKey = "nav_menu_{$user->id}_{$dashboard}";
        
        return Cache::remember($cacheKey, 3600, function () use ($user, $dashboard) {
            $menus = NavMenu::where('target_dashboard', $dashboard)
                ->where('is_active', true)
                ->with(['subMenus' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('order')
                ->get();

            return $menus->filter(function ($menu) use ($user) {
                // If menu has a specific permission, user must have it
                if ($menu->permission_name && !$user->can($menu->permission_name)) {
                    // Check if they at least have access to one of the submenus
                    $hasAllowedSubMenu = $menu->subMenus->some(fn($sub) => 
                        !$sub->permission_name || $user->can($sub->permission_name)
                    );
                    
                    if (!$hasAllowedSubMenu) return false;
                }

                // Filter submenus based on permissions
                $menu->setRelation('subMenus', $menu->subMenus->filter(function ($sub) use ($user) {
                    return !$sub->permission_name || $user->can($sub->permission_name);
                }));

                return true;
            });
        });
    }

    /**
     * Clear navigation cache for a user.
     */
    public function clearCache(int $userId): void
    {
        Cache::forget("nav_menu_{$userId}_kecamatan");
        Cache::forget("nav_menu_{$userId}_desa");
    }
}
