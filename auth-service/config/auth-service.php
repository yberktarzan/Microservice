<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains authentication-specific configuration for the
    | Auth microservice. These settings control token expiration,
    | security policies, and other auth-related behaviors.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Token Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for API tokens including expiration times and limits.
    |
    */
    'token' => [
        'expiration' => env('AUTH_TOKEN_EXPIRATION', 60 * 24), // 24 hours in minutes
        'refresh_expiration' => env('AUTH_REFRESH_TOKEN_EXPIRATION', 60 * 24 * 7), // 7 days in minutes
        'personal_access_tokens_expire' => env('AUTH_PERSONAL_ACCESS_TOKENS_EXPIRE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security-related settings for authentication.
    |
    */
    'security' => [
        'login_attempts' => env('AUTH_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('AUTH_LOCKOUT_DURATION', 60), // minutes
        'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800), // 3 hours in seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Verification Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for email verification process.
    |
    */
    'email_verification' => [
        'required' => env('AUTH_EMAIL_VERIFICATION_REQUIRED', true),
        'expires' => env('AUTH_EMAIL_VERIFICATION_EXPIRES', 60), // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Reset Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for password reset functionality.
    |
    */
    'password_reset' => [
        'expires' => env('AUTH_PASSWORD_RESET_EXPIRES', 60), // minutes
        'throttle' => env('AUTH_PASSWORD_RESET_THROTTLE', 60), // seconds between requests
    ],
];
