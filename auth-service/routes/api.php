<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/health', function () {
    return response()->json([
        'service' => 'Auth Service',
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
    ]);
});

// Authentication routes with locale middleware
Route::middleware(['locale'])->group(function () {

    // Public auth routes (no authentication required)
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/forgot-password', 'forgotPassword');
        Route::post('/reset-password', 'resetPassword');
        Route::post('/verify-email', 'verifyEmail')->name('verification.verify');
    });

    // Protected auth routes (authentication required)
    Route::middleware('auth:sanctum')->controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('/logout', 'logout');
        Route::post('/refresh', 'refresh');
        Route::get('/me', 'me');
        Route::post('/resend-verification', 'resendVerification');
    });

    // Legacy user endpoint (for backward compatibility)
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => ['user' => $request->user()],
        ]);
    });
});
