<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Elasticsearch\ElasticsearchService;
use Illuminate\Console\Command;

/**
 * Elasticsearch management command.
 *
 * Provides utilities for managing Elasticsearch indices,
 * health checks, and maintenance operations.
 */
class ElasticsearchCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'elasticsearch:manage 
                            {action : The action to perform (health|indices|create|clean)}
                            {--index= : Specific index name for operations}
                            {--days=30 : Number of days for retention operations}
                            {--force : Force operation without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Manage Elasticsearch indices and operations';

    private ElasticsearchService $elasticsearch;

    public function __construct(ElasticsearchService $elasticsearch)
    {
        parent::__construct();
        $this->elasticsearch = $elasticsearch;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        if (! $this->elasticsearch->isAvailable()) {
            $this->error('Elasticsearch is not available!');
            $this->info('Please check your Elasticsearch configuration and ensure the service is running.');

            return self::FAILURE;
        }

        return match ($action) {
            'health' => $this->checkHealth(),
            'indices' => $this->listIndices(),
            'create' => $this->createIndices(),
            'clean' => $this->cleanOldData(),
            default => $this->showHelp(),
        };
    }

    /**
     * Check Elasticsearch health.
     */
    private function checkHealth(): int
    {
        try {
            $client = $this->elasticsearch->getClient();

            $this->info('Checking Elasticsearch health...');

            // Ping test
            $pingResult = $client->ping();
            $this->info('✅ Ping: '.($pingResult->asBool() ? 'OK' : 'Failed'));

            // Cluster health
            $health = $client->cluster()->health()->asArray();
            $this->info("Cluster: {$health['cluster_name']}");
            $this->info("Status: {$health['status']}");
            $this->info("Nodes: {$health['number_of_nodes']}");
            $this->info("Active Shards: {$health['active_shards']}");

            // Index stats
            $indicesResponse = $client->cat()->indices(['format' => 'json']);
            $indices = $indicesResponse->asArray();
            $this->info('Total Indices: '.count($indices));

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Health check failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * List all indices.
     */
    private function listIndices(): int
    {
        try {
            $client = $this->elasticsearch->getClient();
            $indicesResponse = $client->cat()->indices(['format' => 'json']);
            $indices = $indicesResponse->asArray();

            $this->info('Elasticsearch Indices:');
            $this->table(
                ['Index', 'Documents', 'Size', 'Status'],
                collect($indices)->map(function ($index) {
                    return [
                        $index['index'],
                        $index['docs.count'] ?? '0',
                        $index['store.size'] ?? '0b',
                        $index['status'] ?? 'unknown',
                    ];
                })->toArray()
            );

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to list indices: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Create auth service indices.
     */
    private function createIndices(): int
    {
        $this->info('Creating auth service indices...');

        $indices = [
            config('services.elasticsearch.api_index', 'auth-api-responses'),
            config('services.elasticsearch.auth_index', 'auth-events'),
            config('services.elasticsearch.error_index', 'auth-errors'),
        ];

        foreach ($indices as $index) {
            try {
                $success = $this->elasticsearch->createIndexIfNotExists($index);
                if ($success) {
                    $this->info("✅ Index created/verified: {$index}");
                } else {
                    $this->warn("⚠️  Could not create index: {$index}");
                }
            } catch (\Exception $e) {
                $this->error("❌ Failed to create index {$index}: ".$e->getMessage());
            }
        }

        return self::SUCCESS;
    }

    /**
     * Clean old data based on retention policy.
     */
    private function cleanOldData(): int
    {
        $days = (int) $this->option('days');
        $force = $this->option('force');

        if (! $force) {
            if (! $this->confirm("Are you sure you want to delete data older than {$days} days?")) {
                $this->info('Operation cancelled.');

                return self::SUCCESS;
            }
        }

        $this->info("🧹 Cleaning data older than {$days} days...");

        try {
            $client = $this->elasticsearch->getClient();
            $cutoffDate = now()->subDays($days)->toISOString();

            $indices = [
                config('services.elasticsearch.api_index', 'auth-api-responses'),
                config('services.elasticsearch.auth_index', 'auth-events'),
                config('services.elasticsearch.error_index', 'auth-errors'),
            ];

            foreach ($indices as $index) {
                $result = $client->deleteByQuery([
                    'index' => $index,
                    'body' => [
                        'query' => [
                            'range' => [
                                '@timestamp' => [
                                    'lt' => $cutoffDate,
                                ],
                            ],
                        ],
                    ],
                ]);

                $deleted = $result['deleted'] ?? 0;
                $this->info("Deleted {$deleted} documents from {$index}");
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Cleanup failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Show command help.
     */
    private function showHelp(): int
    {
        $this->info('Elasticsearch Management Commands:');
        $this->info('');
        $this->info('  health   - Check Elasticsearch health and status');
        $this->info('  indices  - List all indices with stats');
        $this->info('  create   - Create auth service indices');
        $this->info('  clean    - Clean old data (use --days=X --force)');
        $this->info('');
        $this->info('Examples:');
        $this->info('  php artisan elasticsearch:manage health');
        $this->info('  php artisan elasticsearch:manage indices');
        $this->info('  php artisan elasticsearch:manage create');
        $this->info('  php artisan elasticsearch:manage clean --days=7 --force');

        return self::SUCCESS;
    }
}
