<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            'Shift 1 (Pagi)',
            'Shift 2 (Sore)',
            'Shift 3 (Malam)',
        ];

        foreach ($shifts as $shiftName) {
            Shift::firstOrCreate(['name' => $shiftName]);
        }

        $this->command->info('✓ Berhasil membuat ' . count($shifts) . ' shift kerja');
    }
}
