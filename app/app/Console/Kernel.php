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

        // Automated Google Drive Backup
        try {
            $profile = \App\Models\AppProfile::first();
            if ($profile && $profile->is_backup_active) {
                $backupTask = $schedule->command('backup:run --only-db --disable-notifications');
                
                switch ($profile->backup_frequency) {
                    case 'daily':
                        $backupTask->dailyAt('01:00');
                        break;
                    case 'weekly':
                        $backupTask->weeklyOn(1, '01:00'); // Mondays
                        break;
                    case 'monthly':
                        $backupTask->monthlyOn(1, '01:00'); // 1st of month
                        break;
                    default:
                        $backupTask->dailyAt('01:00');
                }
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
