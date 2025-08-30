<?php

declare(strict_types=1);

namespace App\Services\Elasticsearch;

use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Client;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Elasticsearch client service for auth microservice.
 * 
 * Provides a centralized way to interact with Elasticsearch
 * with proper error handling and configuration management.
 */
class ElasticsearchService
{
    private Client $client;
    private bool $isAvailable = false;

    public function __construct()
    {
        $this->initializeClient();
    }

    /**
     * Initialize Elasticsearch client with health check.
     */
    private function initializeClient(): void
    {
        try {
            $this->client = ClientBuilder::create()
                ->setHosts([config('services.elasticsearch.host', 'http://localhost:9200')])
                ->build();

            // Health check
            $this->client->ping();
            $this->isAvailable = true;

        } catch (Exception $e) {
            $this->isAvailable = false;
            Log::warning('Elasticsearch is not available', [
                'error' => $e->getMessage(),
                'host' => config('services.elasticsearch.host', 'http://localhost:9200')
            ]);
        }
    }

    /**
     * Check if Elasticsearch is available.
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * Get Elasticsearch client instance.
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Index a document safely with error handling.
     */
    public function indexDocument(string $index, array $document, ?string $id = null): bool
    {
        if (!$this->isAvailable) {
            return false;
        }

        try {
            $params = [
                'index' => $index,
                'body' => array_merge($document, [
                    '@timestamp' => now()->toISOString(),
                    'service' => 'auth-service',
                    'environment' => config('app.env')
                ])
            ];

            if ($id) {
                $params['id'] = $id;
            }

            $this->client->index($params);
            return true;

        } catch (Exception $e) {
            Log::error('Failed to index document to Elasticsearch', [
                'index' => $index,
                'error' => $e->getMessage(),
                'document_keys' => array_keys($document)
            ]);
            return false;
        }
    }

    /**
     * Search documents with error handling.
     */
    public function search(string $index, array $query): ?array
    {
        if (!$this->isAvailable) {
            return null;
        }

        try {
            $response = $this->client->search([
                'index' => $index,
                'body' => $query
            ]);

            return $response->asArray();

        } catch (Exception $e) {
            Log::error('Elasticsearch search failed', [
                'index' => $index,
                'error' => $e->getMessage(),
                'query' => $query
            ]);
            return null;
        }
    }

    /**
     * Create index with mapping if it doesn't exist.
     */
    public function createIndexIfNotExists(string $index, array $mapping = []): bool
    {
        if (!$this->isAvailable) {
            return false;
        }

        try {
            if (!$this->client->indices()->exists(['index' => $index])->asBool()) {
                $params = ['index' => $index];
                
                if (!empty($mapping)) {
                    $params['body'] = $mapping;
                }

                $this->client->indices()->create($params);
                Log::info("Elasticsearch index created: {$index}");
            }
            return true;

        } catch (Exception $e) {
            Log::error('Failed to create Elasticsearch index', [
                'index' => $index,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
