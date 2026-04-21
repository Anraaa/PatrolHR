<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Pintu Utama',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'radius_meters' => 50,
            ],
            [
                'name' => 'Lobi Gedung A',
                'latitude' => -6.2090,
                'longitude' => 106.8460,
                'radius_meters' => 30,
            ],
            [
                'name' => 'Parkir Area B',
                'latitude' => -6.2095,
                'longitude' => 106.8450,
                'radius_meters' => 75,
            ],
            [
                'name' => 'Tangga Darurat',
                'latitude' => -6.2085,
                'longitude' => 106.8470,
                'radius_meters' => 20,
            ],
            [
                'name' => 'Ruang Server',
                'latitude' => -6.2092,
                'longitude' => 106.8455,
                'radius_meters' => 15,
            ],
            [
                'name' => 'Atap Gedung',
                'latitude' => -6.2088,
                'longitude' => 106.8465,
                'radius_meters' => 100,
            ],
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate(
                ['name' => $location['name']],
                array_merge($location, ['uuid' => Str::uuid()->toString()])
            );
        }

        $this->command->info('✓ Berhasil membuat ' . count($locations) . ' lokasi patrol');
    }
}
