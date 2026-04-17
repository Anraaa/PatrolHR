<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\User;
use Illuminate\Console\Command;

class CheckPatrolAlert extends Command
{
    protected $signature = 'patrol:check-alert';
    protected $description = 'Kirim alert ke super_admin jika petugas patrol belum melakukan patrol sampai jam 13:30';

    public function handle(): int
    {
        $today = now()->toDateString();

        // Ambil semua user yang punya role patrol
        $patrolUsers = User::role('patrol')->get();

        if ($patrolUsers->isEmpty()) {
            // Fallback: ambil user yang pernah patrol
            $patrolUsers = User::whereHas('patrols')->get();
        }

        // Ambil super_admin users sebagai penerima alert
        $admins = User::role('super_admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('Tidak ada user dengan role super_admin.');
            return self::SUCCESS;
        }

        $alertCount = 0;

        foreach ($patrolUsers as $user) {
            // Cek apakah user sudah patrol hari ini
            $hasPatrolToday = $user->patrols()
                ->whereDate('patrol_time', $today)
                ->exists();

            if ($hasPatrolToday) {
                continue;
            }

            // Cek apakah alert sudah pernah dikirim hari ini untuk user ini
            $alreadyAlerted = Alert::where('user_id', $user->id)
                ->whereNull('patrol_id')
                ->whereDate('created_at', $today)
                ->where('message', 'like', '%belum melakukan patroli%')
                ->exists();

            if ($alreadyAlerted) {
                continue;
            }

            // Kirim alert ke setiap admin
            $message = "⚠️ Petugas patrol \"{$user->name}\" belum melakukan patroli pada tanggal "
                . now()->translatedFormat('d F Y') . " hingga pukul " . now()->format('H:i') . ".";

            foreach ($admins as $admin) {
                Alert::create([
                    'user_id'   => $user->id,
                    'patrol_id' => null,
                    'message'   => $message,
                    'status'    => 'sent',
                ]);
            }

            $alertCount++;
            $this->info("Alert: {$user->name} belum patrol.");
        }

        $this->info("Selesai. {$alertCount} alert dikirim.");

        return self::SUCCESS;
    }
}
