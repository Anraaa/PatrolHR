<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Alert jika petugas patrol belum patrol sampai jam 13:30
Schedule::command('patrol:check-alert')->dailyAt('13:30')->timezone('Asia/Jakarta');
