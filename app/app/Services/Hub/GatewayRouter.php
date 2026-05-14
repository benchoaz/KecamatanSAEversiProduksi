<?php

namespace App\Services\Hub;

use App\Models\Hub\District;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GatewayRouter
{
    /**
     * Route the application to a specific district based on the request.
     */
    public function routeByRequest($request)
    {
        $host = $request->getHost();

        // Contoh: paiton.kecamatanbesuk.web.id atau paiton.id
        $slug = explode('.', $host)[0];

        return $this->switchToDistrict($slug);
    }

    /**
     * Switch the database connection to a specific district.
     */
    public function switchToDistrict(string $slug)
    {
        $district = District::where('slug', $slug)->where('is_active', true)->first();

        if (! $district) {
            Log::warning("GatewayRouter: District not found or inactive for slug: {$slug}");

            return false;
        }

        $this->configureTenantConnection($district);

        return $district;
    }

    /**
     * Dynamically configure the database connection for the tenant.
     */
    protected function configureTenantConnection(District $district)
    {
        $connectionName = 'tenant';

        // Salin konfigurasi default pgsql ke koneksi 'tenant'
        $config = Config::get('database.connections.pgsql');

        // Override dengan data spesifik kecamatan
        $config['host'] = $district->db_host ?: $config['host'];
        $config['database'] = $district->db_name ?: $config['database'];
        $config['username'] = $district->db_user ?: $config['username'];
        $config['password'] = $district->db_pass ?: $config['password'];

        Config::set("database.connections.{$connectionName}", $config);

        // Jadikan koneksi 'tenant' sebagai koneksi default untuk sisa request ini
        DB::purge($connectionName);
        DB::setDefaultConnection($connectionName);

        Log::info("GatewayRouter: Switched to district database: {$district->name} ({$district->db_name})");
    }
}
