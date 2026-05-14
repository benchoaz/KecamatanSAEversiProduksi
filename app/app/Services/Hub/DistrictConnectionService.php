<?php

namespace App\Services\Hub;

use App\Models\Hub\HubDistrict;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DistrictConnectionService
 *
 * "Jembatan" dari Hub ke database masing-masing kecamatan.
 * Cara pakai: $conn = app(DistrictConnectionService::class)->connect($district);
 * Lalu: $conn->table('public_services')->count();
 */
class DistrictConnectionService
{
    /**
     * Buat koneksi dinamis ke database kecamatan tertentu.
     * Mengembalikan DB connection yang siap dipakai untuk query.
     */
    public function connect(HubDistrict $district): ?\Illuminate\Database\Connection
    {
        // Jika kecamatan tidak aktif atau tidak punya info DB, skip
        if (! $district->is_active || empty($district->db_name)) {
            return null;
        }

        $connectionName = 'district_'.$district->slug;

        // Daftarkan koneksi baru ke Laravel config secara runtime
        Config::set("database.connections.{$connectionName}", [
            'driver' => 'pgsql',
            'host' => $district->db_host ?? env('DB_HOST', '127.0.0.1'),
            'port' => $district->db_port ?? env('DB_PORT', '5432'),
            'database' => $district->db_name,
            'username' => $district->db_user ?? env('DB_USERNAME', 'postgres'),
            'password' => $district->db_pass ?? env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
        ]);

        try {
            // Test koneksi
            DB::connection($connectionName)->getPdo();

            return DB::connection($connectionName);
        } catch (\Exception $e) {
            Log::warning("Hub: Gagal konek ke DB kecamatan [{$district->name}]: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Ambil statistik ringkas dari satu kecamatan.
     * Return null jika kecamatan tidak bisa dihubungi.
     */
    public function getStats(HubDistrict $district): ?array
    {
        $conn = $this->connect($district);

        if (! $conn) {
            return null;
        }

        try {
            $total = $conn->table('public_services')->count();
            $pending = $conn->table('public_services')->where('status', 'menunggu')->count();
            $done = $conn->table('public_services')
                ->whereIn('status', ['selesai', 'done'])
                ->count();
            $warga = $conn->table('users')->count();

            return [
                'total_services' => $total,
                'pending' => $pending,
                'done' => $done,
                'total_warga' => $warga,
                'is_reachable' => true,
            ];
        } catch (\Exception $e) {
            Log::warning("Hub: Gagal baca statistik [{$district->name}]: ".$e->getMessage());

            return ['is_reachable' => false];
        }
    }

    /**
     * Ambil statistik dari SEMUA kecamatan aktif sekaligus.
     */
    public function getAllStats(): array
    {
        $districts = HubDistrict::where('is_active', true)->get();
        $results = [];

        foreach ($districts as $district) {
            $stats = $this->getStats($district);
            $results[$district->slug] = array_merge([
                'name' => $district->name,
                'domain' => $district->domain,
                'slug' => $district->slug,
            ], $stats ?? ['is_reachable' => false]);
        }

        return $results;
    }
}
