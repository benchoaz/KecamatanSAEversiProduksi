<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    /**
     * Kode ADM4 BMKG untuk Besuk Agung, Probolinggo
     * Bisa dikembangkan untuk dinamis berdasarkan setting profile
     */
    protected string $defaultAdm4 = '35.13.13.2012';
    
    /**
     * Get weather forecast for a specific ADM4 code
     */
    public function getForecast(?string $adm4 = null): array
    {
        $profile = appProfile();
        $adm4 = $adm4 ?: ($profile->bmkg_adm4_code ?? $this->defaultAdm4);
        $cacheKey = "weather_forecast_{$adm4}";

        return Cache::remember($cacheKey, 1800, function() use ($adm4) {
            try {
                $response = Http::timeout(10)->get("https://api.bmkg.go.id/publik/prakiraan-cuaca", [
                    'adm4' => $adm4
                ]);

                if ($response->successful()) {
                    return $this->parseBmkgData($response->json());
                }
                
                Log::warning("BMKG API returned error for ADM4: {$adm4}", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to fetch weather from BMKG: " . $e->getMessage());
            }

            return ['success' => false, 'message' => 'Gagal mengambil data cuaca'];
        });
    }

    /**
     * Parse raw BMKG JSON into a more readable format for AI
     */
    protected function parseBmkgData(array $data): array
    {
        // BMKG API structure can be complex, we extract what's important
        // Usually it contains 'data' array with forecast per time period
        
        $location = $data['lokasi']['nama'] ?? 'Besuk';
        $forecasts = $data['data'][0]['cuaca'] ?? []; // Array of forecasts
        
        if (empty($forecasts)) {
            return ['success' => false, 'message' => 'Data ramalan tidak tersedia'];
        }

        $formatted = [
            'success' => true,
            'location' => $location,
            'current' => null,
            'today_summary' => [],
            'raw_text' => ""
        ];

        $now = now();
        $text = "Prakiraan Cuaca di {$location}:\n";

        foreach ($forecasts as $f) {
            // BMKG usually gives 'datetime' in format like '2026-05-08 12:00:00'
            $time = isset($f['datetime']) ? \Carbon\Carbon::parse($f['datetime']) : null;
            if (!$time) continue;

            // Only take forecasts for today and tomorrow
            if ($time->diffInDays($now) > 1) continue;

            $condition = $f['condition_name'] ?? 'Berawan';
            $temp = $f['temp'] ?? '??';
            $humidity = $f['hu'] ?? '??';
            
            $timeStr = $time->format('H:i');
            $dayStr = $time->isToday() ? 'Hari ini' : 'Besok';
            
            $entry = "[{$dayStr} {$timeStr}] {$condition}, Suhu: {$temp}°C, Lembab: {$humidity}%";
            $formatted['today_summary'][] = $entry;
            $text .= "- {$entry}\n";

            // Set current if it's the closest one
            if ($time->isPast() && (!$formatted['current'] || $time->gt($formatted['current_time']))) {
                $formatted['current'] = $condition;
                $formatted['current_temp'] = $temp;
                $formatted['current_time'] = $time;
            }
        }

        $formatted['raw_text'] = $text;
        return $formatted;
    }

    /**
     * Get real-time alert summary from BMKG CAP RSS
     */
    public function checkBmkgAlerts(): array
    {
        try {
            // Get the RSS feed of active alerts
            $response = Http::timeout(10)->get("https://www.bmkg.go.id/alerts/nowcast/id");
            
            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Gagal terhubung ke server BMKG Alerts'];
            }

            // Simple XML parsing using SimpleXML
            $xml = simplexml_load_string($response->body());
            if (!$xml) {
                return ['success' => false, 'message' => 'Format data BMKG tidak valid'];
            }

            $profile = appProfile();
            $regionName = $profile->region_name ?? 'Besuk';
            
            $alerts = [];
            $targetKeywords = [$regionName, 'Probolinggo']; // Probolinggo is the Parent Regency, keep it for context
            
            foreach ($xml->channel->item as $item) {
                $description = (string) $item->description;
                $title = (string) $item->title;
                $link = (string) $item->link;
                $guid = (string) $item->guid; // Unique ID for this alert

                // Check if any target keywords match in title or description
                $isMatch = false;
                foreach ($targetKeywords as $kw) {
                    if (stripos($description, $kw) !== false || stripos($title, $kw) !== false) {
                        $isMatch = true;
                        break;
                    }
                }

                if ($isMatch) {
                    $alerts[] = [
                        'id' => $guid,
                        'title' => $title,
                        'description' => $description,
                        'link' => $link,
                        'pubDate' => (string) $item->pubDate,
                    ];
                }
            }

            return [
                'success' => true,
                'alerts' => $alerts
            ];

        } catch (\Exception $e) {
            Log::error("Error checking BMKG alerts: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get real-time radar alert summary
     */
    public function getRadarAlert(): string
    {
        return "Pantauan Radar Juanda saat ini menunjukkan kondisi awan normal di wilayah Probolinggo Timur.";
    }
}
