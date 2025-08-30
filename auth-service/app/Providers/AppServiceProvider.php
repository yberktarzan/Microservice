<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Exceptions\Handler;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Contracts\Debug\ExceptionHandler;
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
        // Exception Handler binding
        $this->app->singleton(ExceptionHandler::class, Handler::class);

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
