<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NavSubMenu extends Model
{
    use HasFactory;

    protected $table = 'navigation_sub_menus';

    protected $fillable = [
        'menu_id',
        'name',
        'slug',
        'route_name',
        'order',
        'permission_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function menu()
    {
        return $this->belongsTo(NavMenu::class, 'menu_id');
    }
}
