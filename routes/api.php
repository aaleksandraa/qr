<?php

use App\Http\Controllers\Api\V1\QrCodeApiController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('v1')->group(function () {
    Route::get('qr-codes', [QrCodeApiController::class, 'index']);
    Route::post('qr-codes', [QrCodeApiController::class, 'store']);
    Route::get('qr-codes/{qr_code}', [QrCodeApiController::class, 'show']);
    Route::put('qr-codes/{qr_code}', [QrCodeApiController::class, 'update']);
    Route::delete('qr-codes/{qr_code}', [QrCodeApiController::class, 'destroy']);
    Route::get('qr-codes/{qr_code}/analytics', [QrCodeApiController::class, 'analytics']);
});
