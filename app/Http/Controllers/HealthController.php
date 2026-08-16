<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app' => true,
            'database' => $this->database(),
            'cache' => $this->cache(),
        ];

        $ok = ! in_array(false, $checks, true);

        return response()->json([
            'status' => $ok ? 'ok' : 'degraded',
            'checks' => $checks,
        ], $ok ? 200 : 503);
    }

    private function database(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function cache(): bool
    {
        try {
            Cache::put('health:ping', '1', 10);

            return Cache::get('health:ping') === '1';
        } catch (Throwable) {
            return false;
        }
    }
}
