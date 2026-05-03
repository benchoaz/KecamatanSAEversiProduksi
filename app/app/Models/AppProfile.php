<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_name',
        'region_name',
        'region_level',
        'tagline',
        'logo_path',
        'image_pariwisata',
        'image_festival',
        'hero_image_path',
        'hero_image_alt',
        'hero_image_active',
        'hero_bg_path',
        'hero_bg_opacity',
        'hero_bg_blur',
        'is_menu_pengaduan_active',
        'is_menu_umkm_active',
        'is_menu_berita_active',
        'is_menu_pelayanan_active',
        'is_menu_statistik_active',
        'address',
        'phone',
        'whatsapp_complaint',
        'whatsapp_bot_number',
        'is_ai_active',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'x_url',
        'leader_name',
        'leader_title',
        'office_hours_mon_thu',
        'office_hours_fri',
        'map_latitude',
        'map_longitude',
        'updated_by',
        'is_operator_notification_enabled',
        // Bot Settings
        'public_url',
        'whatsapp_bot_menu',
        
        // AI Settings
        'ai_provider',
        'openai_api_key',
        'google_api_key',
        'anthropic_api_key',
        'xai_api_key',
        'deepseek_api_key',
        'dashscope_api_key',
        'zhipu_api_key',
        'openrouter_api_key',
        'alpha_vantage_api_key',
    ];

    protected $casts = [
        'whatsapp_bot_menu' => 'array',
        'openai_api_key' => 'encrypted',
        'google_api_key' => 'encrypted',
        'anthropic_api_key' => 'encrypted',
        'xai_api_key' => 'encrypted',
        'deepseek_api_key' => 'encrypted',
        'dashscope_api_key' => 'encrypted',
        'zhipu_api_key' => 'encrypted',
        'openrouter_api_key' => 'encrypted',
        'alpha_vantage_api_key' => 'encrypted',
    ];

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the full region name, ensuring no double prefixes like "Kecamatan Kecamatan Besuk"
     */
    public function getFullRegionNameAttribute()
    {
        $level = ucfirst($this->region_level);
        $name = $this->region_name;

        // Check if the level is already at the beginning of the name (case-insensitive)
        if (stripos(trim($name), $level) === 0) {
            return $name;
        }

        return $level . ' ' . $name;
    }
}
