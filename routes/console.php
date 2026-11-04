<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Batalkan otomatis pesanan yang sudah disetujui tetapi tidak dibayar dalam 60 menit
// (stok dikembalikan dan pergerakan stok dicatat)
Schedule::command('orders:cancel-expired')->everyMinute();

// Sisa fitur Virtual Account (tidak dipakai pada alur QRIS statis; tidak ada data VA)
Schedule::command('va:check-expired')->everyMinute();

// Note: va:expire is deprecated and now just calls va:check-expired