<?php

namespace Database\Seeders;

use App\Models\Patrol;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PatrolQrTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $user = User::where('email', 'admin@admin.com')->first();
        if (!$user) {
            $this->command->info('Admin user tidak ditemukan, skip seeding patrol QR test data');
            return;
        }

        // Get or create sample locations
        $locations = Location::all();
        if ($locations->isEmpty()) {
            $this->command->warn('Tidak ada lokasi ditemukan. Buat lokasi terlebih dahulu.');
            return;
        }

        // Get shifts
        $shifts = Shift::all();
        if ($shifts->isEmpty()) {
            $this->command->warn('Tidak ada shift ditemukan. Buat shift terlebih dahulu.');
            return;
        }

        // Create sample patrol records with QR tokens for testing
        $now = now();
        $patrol_count = 0;

        // Create patrols for the last 7 days
        for ($day = 0; $day < 7; $day++) {
            $date = $now->copy()->subDays($day)->startOfDay();

            foreach ($shifts as $shift) {
                foreach ($locations as $location) {
                    // Randomly decide if patrol was done and validated
                    $patrolDone = rand(0, 1) === 1;
                    if (!$patrolDone) {
                        continue; // Skip some to show incomplete patrols
                    }

                    $patrolTime = $date->copy()->addHours(rand(7, 22));

                    $qrToken = Str::random(32);
                    $qrScannedAt = null;
                    $qrScannedIp = null;

                    // Randomly decide if QR was scanned
                    $qrScanned = rand(0, 1) === 1;
                    if ($qrScanned) {
                        $qrScannedAt = $patrolTime->copy()->addMinutes(rand(5, 30));
                        $qrScannedIp = $this->generateRandomIP();
                    }

                    Patrol::create([
                        'user_id' => $user->id,
                        'employee_id' => null,
                        'shift_id' => $shift->id,
                        'location_id' => $location->id,
                        'violation_id' => null,
                        'action_id' => null,
                        'description' => 'Patrol rutin - tidak ada temuan',
                        'patrol_time' => $patrolTime,
                        'qr_code_token' => $qrToken,
                        'qr_scanned_at' => $qrScannedAt,
                        'qr_scanned_ip' => $qrScannedIp,
                    ]);

                    $patrol_count++;
                }
            }
        }

        $this->command->info("✓ Berhasil membuat {$patrol_count} record patrol test dengan QR code validation");
    }

    /**
     * Generate random IP address
     */
    private function generateRandomIP(): string
    {
        return implode('.', [
            rand(192, 223),
            rand(0, 255),
            rand(0, 255),
            rand(1, 254),
        ]);
    }
}
