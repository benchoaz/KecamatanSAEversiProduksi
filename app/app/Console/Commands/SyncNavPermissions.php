<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SyncNavPermissions extends Command
{
    protected $signature = 'nav:sync-permissions';
    protected $description = 'Sync all NavMenu permission_names into Spatie permissions table';

    public function handle()
    {
        $this->info('Syncing NavMenu permissions to Spatie...');

        // Collect all permission_name values from nav_menus and nav_sub_menus
        $menuPerms = \DB::table('nav_menus')
            ->whereNotNull('permission_name')
            ->where('permission_name', '!=', '')
            ->pluck('permission_name');

        $subMenuPerms = \DB::table('nav_sub_menus')
            ->whereNotNull('permission_name')
            ->where('permission_name', '!=', '')
            ->pluck('permission_name');

        $allPerms = $menuPerms->merge($subMenuPerms)->unique()->filter();

        $created = 0;
        $existing = 0;

        foreach ($allPerms as $permName) {
            $perm = Permission::firstOrCreate([
                'name'       => $permName,
                'guard_name' => 'web',
            ]);

            if ($perm->wasRecentlyCreated) {
                $this->line("  <fg=green>CREATED</> : {$permName}");
                $created++;
            } else {
                $this->line("  <fg=gray>EXISTS</>  : {$permName}");
                $existing++;
            }
        }

        // Flush Spatie's internal cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info("Done! Created: {$created}, Already existed: {$existing}");
        return Command::SUCCESS;
    }
}
