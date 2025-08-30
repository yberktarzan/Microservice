<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;

/**
 * Health Check Service Provider for Auth Microservice.
 *
 * Configures health checks specific to authentication service requirements.
 */
class HealthServiceProvider extends ServiceProvider
{
    /**
     * Register health checks for auth microservice.
     */
    public function boot(): void
    {
        Health::checks([
            // Database check - critical for auth service
            DatabaseCheck::new(),
            
            // Cache check - important for sessions/tokens
            CacheCheck::new(),
            
            // Environment checks
            DebugModeCheck::new(),
            
            // Disk space check
            UsedDiskSpaceCheck::new(),
        ]);
    }
}
