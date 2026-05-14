<?php

namespace App\Console\Commands;

use App\Models\WahaN8nSetting;
use App\Services\WeatherService;
use App\Services\WhatsApp\WhatsAppManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckWeatherAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-weather-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check BMKG CAP RSS for extreme weather alerts and broadcast to WhatsApp groups';

    /**
     * Execute the console command.
     */
    public function handle(WeatherService $weatherService)
    {
        $settings = WahaN8nSetting::first();

        if (! $settings || ! $settings->is_weather_alert_enabled) {
            $this->info('Weather alerts are disabled.');

            return 0;
        }

        $groupRaw = $settings->broadcast_group_ids;
        if (empty($groupRaw)) {
            $this->warn('No broadcast groups configured.');

            return 0;
        }

        // Parse group IDs (comma separated)
        $groups = array_map('trim', explode(',', $groupRaw));
        $groups = array_filter($groups, fn ($id) => str_contains($id, '@g.us'));

        if (empty($groups)) {
            $this->warn('No valid WhatsApp group IDs found (@g.us).');

            return 0;
        }

        $this->info('Checking BMKG for alerts...');
        $result = $weatherService->checkBmkgAlerts();

        if (! $result['success'] || empty($result['alerts'])) {
            $this->info('No matching alerts found.');

            return 0;
        }

        foreach ($result['alerts'] as $alert) {
            // Check if we already broadcasted this alert
            if ($settings->last_alert_id === $alert['id']) {
                $this->info("Alert {$alert['id']} already broadcasted.");

                continue;
            }

            $this->info('Processing Alert: '.$alert['title']);

            $message = "⚠️ *PERINGATAN DINI CUACA EKSTREM* ⚠️\n\n";
            $message .= "*{$alert['title']}*\n\n";
            $message .= $alert['description']."\n\n";
            $message .= '📅 Waktu: '.$alert['pubDate']."\n";
            $message .= '🔗 Info Detail: '.$alert['link']."\n\n";
            $message .= '_Sumber: BMKG Indonesia_';

            $whatsapp = WhatsAppManager::driver();

            foreach ($groups as $groupId) {
                try {
                    $this->info("Sending to group: {$groupId}");
                    $whatsapp->sendMessage($groupId, $message);
                } catch (\Exception $e) {
                    Log::error("Failed to send weather alert to group {$groupId}: ".$e->getMessage());
                }
            }

            // Update last alert ID to avoid double sending
            $settings->update([
                'last_alert_id' => $alert['id'],
                'last_weather_alert_check' => now(),
            ]);
        }

        $this->info('Weather alert check completed.');

        return 0;
    }
}
