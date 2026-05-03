<?php

namespace App\Http\Controllers;

use App\Models\AppProfile;
use App\Services\ApplicationProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicationProfileController extends Controller
{
    protected $profileService;

    public function __construct(ApplicationProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function index()
    {
        $profile = $this->profileService->getProfile();
        return view('kecamatan.settings.profile', compact('profile'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:100',
            'region_name' => 'required|string|max:100',
            'region_level' => 'required|in:desa,kecamatan,kabupaten',
            'tagline' => 'nullable|string|max:200',
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image_pariwisata' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'image_festival' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'hero_image_path' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048', // Limit 2MB, support JPG/JPEG/PNG/WebP
            'hero_image_alt' => 'nullable|string|max:100',
            'hero_image_active' => 'nullable|in:0,1,on',
            'hero_bg_path' => 'nullable|image|mimes:jpeg,png,jpg|max:3072', // Max 3MB for background
            'hero_bg_opacity' => 'nullable|integer|min:0|max:100',
            'hero_bg_blur' => 'nullable|integer|min:0|max:20',
            'is_menu_pengaduan_active' => 'nullable|in:0,1,on',
            'is_menu_umkm_active' => 'nullable|in:0,1,on',
            'is_menu_berita_active' => 'nullable|in:0,1,on',
            'is_menu_pelayanan_active' => 'nullable|in:0,1,on',
            'is_menu_statistik_active' => 'nullable|in:0,1,on',
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:50',
            'whatsapp_complaint' => 'nullable|string|max:50',
            'is_ai_active' => 'nullable|boolean',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'x_url' => 'nullable|url|max:255',
            'leader_name' => 'nullable|string|max:255',
            'leader_title' => 'nullable|string|max:255',
            'office_hours_mon_thu' => 'nullable|string|max:100',
            'office_hours_fri' => 'nullable|string|max:100',
            'map_latitude' => 'nullable|numeric|between:-90,90',
            'map_longitude' => 'nullable|numeric|between:-180,180',
            'public_url' => 'nullable|url|max:500',
            'whatsapp_bot_menu' => 'nullable|array',
            'whatsapp_bot_menu.*.label' => 'nullable|string|max:100',
            'whatsapp_bot_menu.*.description' => 'nullable|string|max:255',
            'whatsapp_bot_menu.*.action' => 'nullable|string|max:100',
            'whatsapp_bot_menu.*.enabled' => 'nullable',
            'is_operator_notification_enabled' => 'nullable|in:0,1,on',
            'ai_provider' => 'nullable|string',
            'openai_api_key' => 'nullable|string',
            'google_api_key' => 'nullable|string',
            'anthropic_api_key' => 'nullable|string',
            'xai_api_key' => 'nullable|string',
            'deepseek_api_key' => 'nullable|string',
            'dashscope_api_key' => 'nullable|string',
            'zhipu_api_key' => 'nullable|string',
            'openrouter_api_key' => 'nullable|string',
            'alpha_vantage_api_key' => 'nullable|string',
        ]);

        $profile = AppProfile::first() ?? new AppProfile();

        $data = $request->only([
            'app_name',
            'region_name',
            'region_level',
            'tagline',
            'hero_image_alt',
            'hero_bg_opacity',
            'hero_bg_blur',
            'address',
            'phone',
            'whatsapp_complaint',
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
            'public_url',
            'is_operator_notification_enabled',
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
        ]);
        $data['hero_image_active'] = $request->has('hero_image_active') ? true : false;
        $data['is_menu_pengaduan_active'] = $request->has('is_menu_pengaduan_active') ? true : false;
        $data['is_menu_umkm_active'] = $request->has('is_menu_umkm_active') ? true : false;
        $data['is_menu_berita_active'] = $request->has('is_menu_berita_active') ? true : false;
        $data['is_menu_pelayanan_active'] = $request->has('is_menu_pelayanan_active') ? true : false;
        $data['is_menu_statistik_active'] = $request->has('is_menu_statistik_active') ? true : false;
        $data['is_operator_notification_enabled'] = $request->has('is_operator_notification_enabled') ? true : false;
        $data['updated_by'] = auth()->id();

        // Process whatsapp_bot_menu: normalize enabled field (checkbox only sends when checked)
        if ($request->has('whatsapp_bot_menu')) {
            $menuItems = $request->input('whatsapp_bot_menu', []);
            foreach ($menuItems as $i => $item) {
                $menuItems[$i]['enabled'] = !empty($item['enabled']);
                $menuItems[$i]['number']  = (string)($i + 1);
            }
            $data['whatsapp_bot_menu'] = $menuItems;
        }

        // Handle File Uploads
        $fileFields = [
            'logo_path' => 'logo_path',
            'image_pariwisata' => 'image_pariwisata',
            'image_festival' => 'image_festival',
            'hero_image_path' => 'hero_image_path',
            'hero_bg_path' => 'hero_bg_path'
        ];

        foreach ($fileFields as $requestKey => $dbColumn) {
            if ($request->hasFile($requestKey)) {
                // Delete old file if exists
                if ($profile->$dbColumn) {
                    Storage::disk('public')->delete($profile->$dbColumn);
                }

                $path = 'app';
                if ($requestKey === 'logo_path') {
                    $path = 'logos';
                }
                if ($requestKey === 'hero_image_path') {
                    $path = 'media';
                }
                if ($requestKey === 'hero_bg_path') {
                    $path = 'backgrounds';
                }

                $data[$dbColumn] = $request->file($requestKey)->store($path, 'public');
            }
        }

        $profile->fill($data);
        $profile->save();

        $this->profileService->clearCache();

        return redirect()->back()->with('success', 'Identitas aplikasi berhasil diperbarui.');
    }

    public function features()
    {
        $menus = \App\Models\Menu::orderBy('urutan')->get();
        return view('kecamatan.settings.features', compact('menus'));
    }

    public function toggleFeature(Request $request)
    {
        $request->validate([
            'kode_menu' => 'required|string|exists:menu,kode_menu',
            'is_active' => 'required|boolean',
        ]);

        $menu = \App\Models\Menu::where('kode_menu', $request->kode_menu)->first();
        $menu->is_active = $request->is_active;
        $menu->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Status fitur ' . $menu->nama_menu . ' berhasil diperbarui.'
        ]);
    }

    public function testApiKey(Request $request)
    {
        $provider = $request->input('provider');
        $apiKey = $request->input('api_key');

        if (empty($apiKey)) {
            return response()->json(['success' => false, 'message' => 'API Key kosong.']);
        }

        try {
            if ($provider === 'gemini') {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
                $response = \Illuminate\Support\Facades\Http::timeout(10)->post($url, [
                    'contents' => [['parts' => [['text' => 'Pesan percobaan uji koneksi. Balas OK.']]]]
                ]);
                if ($response->successful()) return response()->json(['success' => true]);
                return response()->json(['success' => false, 'message' => 'Kunci Gemini tidak valid.']);
            } 
            elseif ($provider === 'openai') {
                $response = \Illuminate\Support\Facades\Http::withHeaders(['Authorization' => "Bearer {$apiKey}"])
                    ->timeout(10)->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [['role' => 'user', 'content' => 'Test']]
                    ]);
                if ($response->successful()) return response()->json(['success' => true]);
                return response()->json(['success' => false, 'message' => 'Kunci OpenAI tidak valid.']);
            }
            // For Deepseek, xAI, OpenRouter, DashScope
            else {
                $baseUrl = '';
                if ($provider === 'deepseek') $baseUrl = 'https://api.deepseek.com/chat/completions';
                elseif ($provider === 'xai') $baseUrl = 'https://api.x.ai/v1/chat/completions';
                elseif ($provider === 'openrouter') $baseUrl = 'https://openrouter.ai/api/v1/chat/completions';
                elseif ($provider === 'dashscope') $baseUrl = 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions';
                
                if (!$baseUrl) return response()->json(['success' => false, 'message' => 'Provider tidak valid.']);

                $response = \Illuminate\Support\Facades\Http::withHeaders(['Authorization' => "Bearer {$apiKey}"])
                    ->timeout(10)->post($baseUrl, [
                        'model' => $provider === 'deepseek' ? 'deepseek-chat' : 'grok-beta',
                        'messages' => [['role' => 'user', 'content' => 'Test']]
                    ]);
                if ($response->successful()) return response()->json(['success' => true]);
                return response()->json(['success' => false, 'message' => "Kunci {$provider} tidak valid."]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghubungi server: ' . $e->getMessage()]);
        }
    }
}
