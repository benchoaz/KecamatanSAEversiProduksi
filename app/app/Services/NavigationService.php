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
    public function getMenus($dashboard = 'kecamatan')
    {
        $user = auth()->user();
        if (!$user) return collect([]);

        // DEBUG LOGGING
        \Log::info('NavigationService: Generating menus for user', [
            'username' => $user->username,
            'role' => $user->role->nama_role ?? 'NONE',
            'dashboard' => $dashboard
        ]);

        // Temporarily disabled cache for debugging
        $menus = NavMenu::where('target_dashboard', $dashboard)
            ->where(function($q) {
                $q->where('is_active', true)->orWhere('is_active', 1);
            })
            ->with(['subMenus' => function ($query) {
                $query->where(function($q) {
                    $q->where('is_active', true)->orWhere('is_active', 1);
                });
            }])
            ->orderBy('order')
            ->get();

        \Log::info('NavigationService: Found raw menus', [
            'count' => $menus->count(),
            'menu_ids' => $menus->pluck('id')->toArray()
        ]);

        dd($menus);

            return $menus->filter(function ($menu) use ($user) {
                // Super Admin can see everything
                if ($user->hasRole('Super Admin') || $user->username === 'admin') {
                    return true;
                }

                // If menu has a specific permission, user must have it
                if ($menu->permission_name && !$user->can($menu->permission_name)) {
                    // Check if they at least have explicit access to one of the submenus
                    $hasAllowedSubMenu = $menu->subMenus->some(fn($sub) => 
                        $sub->permission_name && $user->can($sub->permission_name)
                    );
                    
                    if (!$hasAllowedSubMenu) {
                        return false;
                    }
                }

                // Filter submenus based on permissions
                $menu->setRelation('subMenus', $menu->subMenus->filter(function ($sub) use ($user, $menu) {
                    // Admin bypass for submenus
                    if ($user->username === 'admin' || $user->hasRole('Super Admin')) {
                        return true;
                    }

                    // If submenu has specific permission, check it
                    if ($sub->permission_name) {
                        return $user->can($sub->permission_name);
                    }
                    // If no specific permission, it MUST inherit parent's permission
                    if ($menu->permission_name) {
                        return $user->can($menu->permission_name);
                    }
                    return true;
                }));

                // Hide parent menu if all its submenus are filtered out
                return $menu->subMenus->count() > 0 || !$menu->permission_name;
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
