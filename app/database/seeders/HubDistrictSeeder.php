<?php

namespace Database\Seeders;

use App\Models\Hub\HubDistrict;
use Illuminate\Database\Seeder;

class HubDistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $districts = [
            [
                'name' => 'Kecamatan Besuk',
                'slug' => 'besuk',
                'db_name' => 'dashboard_kecamatan',
                'is_active' => true,
            ],
            [
                'name' => 'Kecamatan Kraksaan',
                'slug' => 'kraksaan',
                'db_name' => 'dashboard_kraksaan',
                'is_active' => true,
            ],
            [
                'name' => 'Kecamatan Paiton',
                'slug' => 'paiton',
                'db_name' => 'dashboard_paiton',
                'is_active' => false,
            ],
        ];

        foreach ($districts as $district) {
            HubDistrict::updateOrCreate(
                ['slug' => $district['slug']],
                $district
            );
        }
    }
}
