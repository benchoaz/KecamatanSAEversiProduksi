<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PersonilDesa;
use App\Models\DokumenDesa;
use App\Models\LembagaDesa;
use Illuminate\Support\Facades\Storage;

class CheckStorageIntegrity extends Command
{
    protected $signature = 'storage:check-integrity';
    protected $description = 'Check if files referenced in DB actually exist on disk';

    public function handle()
    {
        $this->info("Starting Integrity Check...");

        $this->checkTable(PersonilDesa::class, 'file_sk', 'Personil SK');
        $this->checkTable(PersonilDesa::class, 'foto', 'Personil Foto');
        $this->checkTable(DokumenDesa::class, 'file_path', 'Dokumen Desa');
        $this->checkTable(LembagaDesa::class, 'file_sk', 'Lembaga SK');

        $this->info("Check Completed.");
    }

    private function checkTable($modelClass, $column, $label)
    {
        $this->comment("\nChecking $label...");
        $records = $modelClass::whereNotNull($column)->get();
        $total = $records->count();
        $missing = 0;

        foreach ($records as $record) {
            $path = $record->$column;
            $exists = false;
            
            $disks = ['local', 'public'];
            $prefixes = ['', 'local/', 'public/', 'app/', 'storage/app/'];

            foreach ($disks as $disk) {
                foreach ($prefixes as $prefix) {
                    if (Storage::disk($disk)->exists($prefix . $path)) {
                        $exists = true;
                        break 2;
                    }
                }
            }

            if (!$exists) {
                $this->error("Missing file for ID {$record->id}: $path");
                $missing++;
            }
        }

        $this->line("Summary for $label: $missing / $total files missing.");
    }
}
