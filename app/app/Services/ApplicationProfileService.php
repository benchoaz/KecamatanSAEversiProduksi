<?php

namespace App\Services;

use App\Models\AppProfile;
use Illuminate\Support\Facades\Cache;

class ApplicationProfileService
{
    protected $cacheKey = 'app_profile_global';

    public function getProfile()
    {
        return Cache::rememberForever($this->cacheKey, function () {
            return AppProfile::first() ?? new AppProfile([
                'app_name' => 'Kecamatan SAE',
                'region_name' => 'Kecamatan Besuk',
                'region_level' => 'kecamatan',
                'tagline' => 'Solusi Administrasi Terpadu',
            ]);
        });
    }

    public function getAppName()
    {
        return $this->getProfile()->app_name;
    }

    public function getRegionName()
    {
        return $this->getProfile()->region_name;
    }

    public function getRegionLevel()
    {
        return $this->getProfile()->region_level;
    }

    public function getTagline()
    {
        return $this->getProfile()->tagline;
    }

    public function getLogo()
    {
        $path = $this->getProfile()->logo_path;
        return $path ? asset('storage/' . $path) : null;
    }

    public function getPariwisataImage()
    {
        $path = $this->getProfile()->image_pariwisata;
        return $path ? asset('storage/' . $path) : null;
    }

    public function getFestivalImage()
    {
        $path = $this->getProfile()->image_festival;
        return $path ? asset('storage/' . $path) : null;
    }

    public function getHeroImage()
    {
        $path = $this->getProfile()->hero_image_path;
        return $path ? asset('storage/' . $path) : null;
    }

    public function getHeroImageAlt()
    {
        return $this->getProfile()->hero_image_alt ?? 'Pimpinan Daerah';
    }

    public function isHeroImageActive()
    {
        return (bool) ($this->getProfile()->hero_image_active ?? false);
    }

    public function getHeroBg()
    {
        $path = $this->getProfile()->hero_bg_path;
        return $path ? asset('storage/' . $path) : null;
    }

    public function getHeroBgOpacity()
    {
        return $this->getProfile()->hero_bg_opacity ?? 10;
    }

    public function getHeroBgBlur()
    {
        return $this->getProfile()->hero_bg_blur ?? 6;
    }

    public function getWhatsappBotNumber()
    {
        return $this->getProfile()->whatsapp_bot_number;
    }

    public function getWhatsappBotUrl($text = "Halo, saya butuh informasi.")
    {
        $number = $this->getWhatsappBotNumber();
        if (empty($number)) {
            return '#';
        }

        // Clean number (remove +, spaces, dashes)
        $cleanNumber = preg_replace('/[^0-9]/', '', $number);

        return "https://wa.me/{$cleanNumber}?text=" . urlencode($text);
    }

    public function clearCache()
    {
        Cache::forget($this->cacheKey);
    }

    /**
     * Resolve the public URL dynamically.
     */
    public function getPublicUrl()
    {
        // 1. Get from Cache first
        $profile = $this->getProfile();
        $url = $profile->public_url;

        // 2. If Cache is empty or stuck on localhost, FORCE read from DB directly
        if (empty($url) || str_contains($url, 'localhost')) {
            $direct = AppProfile::select('public_url')->first();
            if ($direct) {
                $url = $direct->public_url;
            }
        }

        // 3. If still localhost or empty, use APP_URL from config
        $configUrl = config('app.url');
        if ((empty($url) || str_contains($url, 'localhost')) && !empty($configUrl) && !str_contains($configUrl, 'localhost')) {
            $url = $configUrl;
        }

        // 4. Final Fallback: Use request host if available
        if (empty($url) || str_contains($url, 'localhost')) {
            $host = request()->getHost();
            if ($host && !str_contains($host, 'localhost') && $host !== '127.0.0.1') {
                $scheme = request()->isSecure() ? 'https' : 'http';
                $url = $scheme . '://' . $host;
            }
        }
        
        // Absolute fallback for Besuk region if everything fails
        if (empty($url) || str_contains($url, 'localhost')) {
            $url = 'https://kecamatanbesuk.my.id';
        }

        return rtrim($url, '/');
    }
}
