import paramiko
import sys

ip = "43.134.166.153"
user = "ubuntu"
password = "nebula-57@-ocean"

commands = [
    "mkdir -p ~/KecamatanSAE/app/storage/framework/{cache,sessions,views} ~/KecamatanSAE/app/storage/logs",
    "sudo chmod -R 777 ~/KecamatanSAE/app/storage",
    "sudo chown -R ubuntu:ubuntu ~/KecamatanSAE/app/storage",
    "cat <<'INNEREOF' > ~/KecamatanSAE/app/app/Services/ApplicationProfileService.php\n<?php\n\nnamespace App\\Services;\n\nuse App\\Models\\AppProfile;\nuse Illuminate\\Support\\Facades\\Cache;\n\nclass ApplicationProfileService\n{\n    protected \$cacheKey = 'app_profile_global';\n\n    public function getProfile()\n    {\n        return Cache::rememberForever(\$this->cacheKey, function () {\n            return AppProfile::first() ?? new AppProfile([\n                'app_name' => 'Kecamatan SAE',\n                'region_name' => 'Kecamatan Besuk',\n                'region_level' => 'kecamatan',\n                'tagline' => 'Solusi Administrasi Terpadu',\n            ]);\n        });\n    }\n\n    public function getAppName()\n    {\n        return \$this->getProfile()->app_name;\n    }\n\n    public function getRegionName()\n    {\n        return \$this->getProfile()->region_name;\n    }\n\n    public function getRegionLevel()\n    {\n        return \$this->getProfile()->region_level;\n    }\n\n    public function getTagline()\n    {\n        return \$this->getProfile()->tagline;\n    }\n\n    public function getLogo()\n    {\n        \$path = \$this->getProfile()->logo_path;\n        return \$path ? asset('storage/' . \$path) : null;\n    }\n\n    public function getPariwisataImage()\n    {\n        \$path = \$this->getProfile()->image_pariwisata;\n        return \$path ? asset('storage/' . \$path) : null;\n    }\n\n    public function getFestivalImage()\n    {\n        \$path = \$this->getProfile()->image_festival;\n        return \$path ? asset('storage/' . \$path) : null;\n    }\n\n    public function getHeroImage()\n    {\n        \$path = \$this->getProfile()->hero_image_path;\n        return \$path ? asset('storage/' . \$path) : null;\n    }\n\n    public function getHeroImageAlt()\n    {\n        return \$this->getProfile()->hero_image_alt ?? 'Pimpinan Daerah';\n    }\n\n    public function isHeroImageActive()\n    {\n        return (bool) (\$this->getProfile()->hero_image_active ?? false);\n    }\n\n    public function getHeroBg()\n    {\n        \$path = \$this->getProfile()->hero_bg_path;\n        return \$path ? asset('storage/' . \$path) : null;\n    }\n\n    public function getHeroBgOpacity()\n    {\n        return \$this->getProfile()->hero_bg_opacity ?? 10;\n    }\n\n    public function getHeroBgBlur()\n    {\n        return \$this->getProfile()->hero_bg_blur ?? 6;\n    }\n\n    public function getWhatsappBotNumber()\n    {\n        return \$this->getProfile()->whatsapp_bot_number;\n    }\n\n    public function getWhatsappBotUrl(\$text = \"Halo, saya butuh informasi.\")\n    {\n        \$number = \$this->getWhatsappBotNumber();\n        if (empty(\$number)) {\n            return '#';\n        }\n\n        // Clean number (remove +, spaces, dashes)\n        \$cleanNumber = preg_replace('/[^0-9]/', '', \$number);\n\n        return \"https://wa.me/{\$cleanNumber}?text=\" . urlencode(\$text);\n    }\n\n    public function clearCache()\n    {\n        Cache::forget(\$this->cacheKey);\n    }\n\n    public function getPublicUrl()\n    {\n        \$profile = \$this->getProfile();\n        \$url = \$profile->public_url;\n\n        if (empty(\$url) || str_contains(\$url, 'localhost')) {\n            \$direct = AppProfile::select('public_url')->first();\n            if (\$direct) {\n                \$url = \$direct->public_url;\n            }\n        }\n\n        if (empty(\$url) || str_contains(\$url, 'localhost')) {\n            \$host = request()->getHost();\n            if (\$host) {\n                \$scheme = request()->isSecure() ? 'https' : 'http';\n                \$url = \$scheme . '://' . \$host;\n            }\n        }\n        \n        if (empty(\$url) || str_contains(\$url, 'localhost')) {\n            \$url = config('app.url', 'https://kecamatanbesuk.web.id');\n        }\n\n        return rtrim(\$url, '/');\n    }\n}\nINNEREOF",
    "cd ~/KecamatanSAE && sudo docker compose -f docker-compose.vps.yml up -d"
]

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(ip, username=user, password=password)
    
    for cmd in commands:
        print(f"Executing: {cmd[:50]}...")
        stdin, stdout, stderr = client.exec_command(cmd)
        print(stdout.read().decode())
        print(stderr.read().decode())
        
    client.close()
    print("Success!")
except Exception as e:
    print(f"Error: {e}")
    sys.exit(1)
