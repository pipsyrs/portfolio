<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Frekuensi backup diatur dari halaman Pengaturan. Pembacaan dibungkus
// try/catch karena scheduler juga berjalan sebelum migrasi pertama dieksekusi.
$schedule = 'daily';

try {
    $schedule = settings('backup_schedule') ?: 'daily';
} catch (Throwable) {
    // Tabel settings belum ada — pakai bawaan.
}

$backup = Schedule::command('db:backup')->withoutOverlapping();

match ($schedule) {
    'hourly' => $backup->hourly(),
    'twice_daily' => $backup->twiceDaily(1, 13),
    'weekly' => $backup->weeklyOn(1, '01:00'),
    'monthly' => $backup->monthlyOn(1, '01:00'),
    default => $backup->dailyAt('01:00'),
};

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
