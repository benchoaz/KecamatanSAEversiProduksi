<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NavMenu extends Model
{
    use HasFactory;

    protected $table = 'navigation_menus';

    protected $fillable = [
        'name',
        'icon',
        'slug',
        'route_name',
        'order',
        'permission_name',
        'is_active',
        'target_dashboard',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subMenus()
    {
        return $this->hasMany(NavSubMenu::class, 'menu_id')->orderBy('order');
    }
}
