<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for API-related functionality including response logging,
    | versioning, and other API-specific settings.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | The default API version to be included in responses.
    |
    */
    'version' => env('API_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Response Logging
    |--------------------------------------------------------------------------
    |
    | Whether to enable API response logging for monitoring and debugging.
    |
    */
    'log_responses' => env('API_LOG_RESPONSES', true),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | API rate limiting configuration.
    |
    */
    'rate_limit' => [
        'requests_per_minute' => env('API_RATE_LIMIT', 60),
        'burst_limit' => env('API_BURST_LIMIT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Request/Response Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for request and response handling.
    |
    */
    'request' => [
        'max_size' => env('API_MAX_REQUEST_SIZE', 1024), // KB
        'timeout' => env('API_REQUEST_TIMEOUT', 30), // seconds
    ],

    'response' => [
        'cache_ttl' => env('API_RESPONSE_CACHE_TTL', 300), // seconds
        'include_metadata' => env('API_INCLUDE_METADATA', true),
    ],
];
