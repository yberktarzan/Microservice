<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Elasticsearch\AuthEventLogger;
use App\Services\Elasticsearch\ElasticsearchService;
use Illuminate\Support\ServiceProvider;

/**
 * Elasticsearch Service Provider
 * 
 * Registers Elasticsearch-related services and bindings.
 * Handles service configuration and dependency injection.
 */
class ElasticsearchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Elasticsearch service as singleton
        $this->app->singleton(ElasticsearchService::class, function ($app) {
            return new ElasticsearchService();
        });

        // Register Auth Event Logger
        $this->app->singleton(AuthEventLogger::class, function ($app) {
            return new AuthEventLogger($app->make(ElasticsearchService::class));
        });

        // Bind interface if needed
        // $this->app->bind(ElasticsearchInterface::class, ElasticsearchService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Boot logic if needed
        if (config('services.elasticsearch.enabled', true)) {
            $this->bootElasticsearchIndices();
        }
    }

    /**
     * Ensure Elasticsearch indices are created on boot.
     */
    private function bootElasticsearchIndices(): void
    {
        if (!app()->runningInConsole()) {
            return;
        }

        try {
            $elasticsearch = app(ElasticsearchService::class);
            
            if ($elasticsearch->isAvailable()) {
                // Indices will be created automatically when services are used
                // This is handled in the individual service classes
            }
        } catch (\Exception $e) {
            // Silently fail during boot to prevent application crashes
            logger()->warning('Elasticsearch initialization failed during boot', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
