<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\QrRedirectRuleController;
use App\Http\Controllers\RedirectController;
use App\Http\Middleware\SetWorkspaceContext;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : Inertia::render('welcome');
})->name('home');

Route::get('/health', HealthController::class)->name('health');

Route::get('/r/{slug}', [RedirectController::class, 'show'])
    ->where('slug', '[A-Za-z0-9][A-Za-z0-9_-]*')
    ->name('qr.redirect.local');

Route::post('/r/{slug}', [RedirectController::class, 'unlock'])
    ->where('slug', '[A-Za-z0-9][A-Za-z0-9_-]*')
    ->name('qr.redirect.unlock');

$shortHost = config('qr.short_host');
if (is_string($shortHost) && $shortHost !== '') {
    Route::domain($shortHost)->group(function () {
        Route::get('/{slug}', [RedirectController::class, 'show'])
            ->where('slug', '[A-Za-z0-9][A-Za-z0-9_-]*')
            ->name('qr.redirect');
        Route::post('/{slug}', [RedirectController::class, 'unlock'])
            ->where('slug', '[A-Za-z0-9][A-Za-z0-9_-]*');
    });
}

Route::middleware(['auth', SetWorkspaceContext::class])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('analytics', AnalyticsController::class)->name('analytics');

    Route::get('qr-codes/preview', [QrCodeController::class, 'preview'])
        ->name('qr-codes.preview');

    Route::resource('qr-codes', QrCodeController::class);
    Route::post('qr-codes/{qr_code}/pause', [QrCodeController::class, 'pause'])->name('qr-codes.pause');
    Route::post('qr-codes/{qr_code}/activate', [QrCodeController::class, 'activate'])->name('qr-codes.activate');
    Route::post('qr-codes/{qr_code}/archive', [QrCodeController::class, 'archive'])->name('qr-codes.archive');
    Route::post('qr-codes/{qr_code}/duplicate', [QrCodeController::class, 'duplicate'])->name('qr-codes.duplicate');
    Route::post('qr-codes/{qr_code}/convert-dynamic', [QrCodeController::class, 'convertToDynamic'])->name('qr-codes.convert-dynamic');
    Route::post('qr-codes/{qr_code}/convert-static', [QrCodeController::class, 'convertToStatic'])->name('qr-codes.convert-static');
    Route::get('qr-codes/{qr_code}/download', [QrCodeController::class, 'download'])->name('qr-codes.download');
    Route::post('qr-codes/{qr_code}/rules', [QrRedirectRuleController::class, 'store'])->name('qr-codes.rules.store');
    Route::delete('qr-codes/{qr_code}/rules/{rule}', [QrRedirectRuleController::class, 'destroy'])->name('qr-codes.rules.destroy');

    Route::resource('campaigns', CampaignController::class)->except(['create', 'edit']);
    Route::resource('folders', FolderController::class)->except(['create', 'edit', 'show']);

    Route::get('settings/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('settings/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('settings/api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
