<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Application service provider for Auth microservice.
 *
 * Registers all service bindings and configurations.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        // Service bindings
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
