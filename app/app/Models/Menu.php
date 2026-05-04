<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function aspek()
    {
        return $this->hasMany(Aspek::class)->orderBy('urutan');
    }

    /**
     * Check if a feature is active with caching for performance
     */
    public static function isActive($kode)
    {
        return \Illuminate\Support\Facades\Cache::rememberForever("menu_active_{$kode}", function () use ($kode) {
            $menu = self::where('kode_menu', $kode)->first();
            return $menu ? $menu->is_active : true;
        });
    }

    /**
     * Clear cache when a menu is updated
     */
    public static function boot()
    {
        parent::boot();
        static::saved(function ($menu) {
            \Illuminate\Support\Facades\Cache::forget("menu_active_{$menu->kode_menu}");
        });
    }
}
