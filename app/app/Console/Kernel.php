<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('desa:sync-demografi')->dailyAt('00:00')->withoutOverlapping();
        
        // Polling APIs & Scraping for news updates
        $schedule->command('scrape:desa-news')->everyTwoHours()->withoutOverlapping();
        $schedule->command('scrape:kecamatan-news')->cron('0 */6 * * *')->withoutOverlapping();
        
        // Check BMKG weather alerts every 15 minutes
        $schedule->command('app:check-weather-alerts')->everyFifteenMinutes()->withoutOverlapping();

        // Automated Google Drive Backup Strategy
        try {
            $profile = \App\Models\AppProfile::first();
            if ($profile && $profile->is_backup_active) {
                // 1. Daily Database Backup (Fast & Critical) - 02:00 AM
                $schedule->command('backup:run --only-db --disable-notifications')->dailyAt('02:00')->withoutOverlapping();
                
                // 2. Weekly Full Backup (Photos, PDFs, DB) - Sunday 03:00 AM
                $schedule->command('backup:run --disable-notifications')->weeklyOn(0, '03:00')->withoutOverlapping();

                // 3. Daily Cleanup (Remove old backups) - 04:00 AM
                $schedule->command('backup:clean')->dailyAt('04:00')->withoutOverlapping();
            }
        } catch (\Exception $e) {
            // Prevent failure if DB is not ready
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
