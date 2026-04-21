<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupcalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sqlFile = base_path('group.sql');
        
        if (!file_exists($sqlFile)) {
            $this->command->error("File group.sql tidak ditemukan di: {$sqlFile}");
            return;
        }

        $sql = file_get_contents($sqlFile);
        
        // Extract hanya bagian INSERT VALUES
        preg_match('/INSERT INTO `groupcal`.*?VALUES\s*(.*?);/s', $sql, $matches);
        
        if (empty($matches[1])) {
            $this->command->error("Tidak dapat menemukan data INSERT dalam file SQL");
            return;
        }

        $valuesString = $matches[1];
        
        // Parse setiap baris data (handle both empty and non-empty shfgroup)
        preg_match_all("/\('([^']+)',\s*'([^']*)'\)/", $valuesString, $dataMatches, PREG_SET_ORDER);
        
        $this->command->info("Menemukan " . count($dataMatches) . " baris data");
        
        // Truncate table terlebih dahulu
        DB::table('groupcal')->truncate();
        
        // Insert data dalam batch
        $batchSize = 500;
        $chunks = array_chunk($dataMatches, $batchSize);
        
        foreach ($chunks as $index => $chunk) {
            $data = [];
            foreach ($chunk as $match) {
                $data[] = [
                    'date_shift' => $match[1],
                    'shfgroup' => $match[2] ?: null,
                ];
            }
            
            DB::table('groupcal')->insert($data);
            
            $this->command->info("Batch " . ($index + 1) . "/" . count($chunks) . " selesai");
        }
        
        $this->command->info("✓ Seeding groupcal selesai!");
    }
}
