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
        $adm4 = $adm4 ?: $this->defaultAdm4;
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
     * Get real-time alert summary (Mock for now, can be connected to radar scraping)
     */
    public function getRadarAlert(): string
    {
        // In the future, this can parse the dataradar-update.json from Juanda
        // For now, we return a general notice or "No intense rain detected"
        return "Pantauan Radar Juanda saat ini menunjukkan kondisi awan normal di wilayah Probolinggo Timur.";
    }
}
