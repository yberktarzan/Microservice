<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Elasticsearch integration in auth service.
    | Used for logging, analytics, and monitoring purposes.
    |
    */

    'elasticsearch' => [
        'host' => env('ELASTICSEARCH_HOST', 'http://localhost:9200'),
        'enabled' => env('ELASTICSEARCH_ENABLED', true),
        
        // Index names for different data types
        'api_index' => env('ELASTICSEARCH_API_INDEX', 'auth-api-responses'),
        'auth_index' => env('ELASTICSEARCH_AUTH_INDEX', 'auth-events'),
        'error_index' => env('ELASTICSEARCH_ERROR_INDEX', 'auth-errors'),
        
        // Index settings
        'settings' => [
            'number_of_shards' => env('ELASTICSEARCH_SHARDS', 1),
            'number_of_replicas' => env('ELASTICSEARCH_REPLICAS', 0),
        ],
        
        // Retention policy (days)
        'retention_days' => env('ELASTICSEARCH_RETENTION_DAYS', 30),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
