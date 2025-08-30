<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Spatie\Health\Http\Controllers\HealthCheckResultsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Auth microservice web routes. Primarily used for health checks
| and service information endpoints.
|
*/

Route::get('/', function () {
    return response()->json([
        'service' => 'Auth Service',
        'status' => 'running',
        'version' => '1.0.0',
        'environment' => app()->environment(),
        'timestamp' => now()->toISOString(),
    ]);
});

Route::get('health', HealthCheckResultsController::class);
