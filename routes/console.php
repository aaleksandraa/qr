<?php

use App\Models\QrScan;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('qr:prune-analytics', function () {
    $days = (int) config('qr.analytics.retention_days', 365);
    $deleted = QrScan::query()->where('scanned_at', '<', now()->subDays($days))->delete();
    $this->info("Removed {$deleted} scan rows older than {$days} days.");
})->purpose('Delete analytics rows past the configured retention period');

Artisan::command('qr:prune-temp', function () {
    Storage::disk('local')->deleteDirectory('qr/exports');
    $this->info('Temporary QR exports cleared.');
})->purpose('Remove generated temporary QR export files');

Schedule::command('qr:prune-analytics')->daily();
Schedule::command('qr:prune-temp')->daily();
