<?php

declare(strict_types=1);

namespace App\Services\Elasticsearch;

use Illuminate\Support\Facades\Auth;

/**
 * Authentication event logger for Elasticsearch.
 *
 * Tracks authentication-related events for security monitoring,
 * analytics, and compliance purposes.
 */
class AuthEventLogger
{
    private ElasticsearchService $elasticsearch;

    private string $indexName;

    public function __construct(ElasticsearchService $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
        $this->indexName = config('services.elasticsearch.auth_index', 'auth-events');

        // Ensure index exists with proper mapping
        $this->createAuthEventsIndex();
    }

    /**
     * Log successful login event.
     */
    public function logLoginSuccess(int $userId, string $email, array $context = []): void
    {
        $this->logAuthEvent('login_success', $userId, $email, $context);
    }

    /**
     * Log failed login attempt.
     */
    public function logLoginFailed(string $email, string $reason, array $context = []): void
    {
        $this->logAuthEvent('login_failed', null, $email, array_merge($context, [
            'failure_reason' => $reason,
        ]));
    }

    /**
     * Log user registration.
     */
    public function logUserRegistered(int $userId, string $email, array $context = []): void
    {
        $this->logAuthEvent('user_registered', $userId, $email, $context);
    }

    /**
     * Log token refresh.
     */
    public function logTokenRefresh(int $userId, string $email, array $context = []): void
    {
        $this->logAuthEvent('token_refresh', $userId, $email, $context);
    }

    /**
     * Log logout event.
     */
    public function logLogout(int $userId, string $email, array $context = []): void
    {
        $this->logAuthEvent('logout', $userId, $email, $context);
    }

    /**
     * Log password change.
     */
    public function logPasswordChanged(int $userId, string $email, array $context = []): void
    {
        $this->logAuthEvent('password_changed', $userId, $email, $context);
    }

    /**
     * Log suspicious activity.
     */
    public function logSuspiciousActivity(string $activityType, ?string $email = null, array $context = []): void
    {
        $this->logAuthEvent('suspicious_activity', null, $email, array_merge($context, [
            'activity_type' => $activityType,
            'severity' => 'high',
        ]));
    }

    /**
     * Generic auth event logger.
     */
    private function logAuthEvent(string $eventType, ?int $userId, ?string $email, array $context = []): void
    {
        $request = request();

        $document = [
            'event_type' => $eventType,
            'user_id' => $userId,
            'email' => $email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_id' => $request->header('X-Request-ID') ?: uniqid('req_'),
            'session_id' => session()->getId(),
            'timestamp' => now()->toISOString(),
            'context' => $context,
            'request_data' => [
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'url' => $request->fullUrl(),
                'headers' => $this->sanitizeHeaders($request->headers->all()),
            ],
        ];

        // Add geolocation data if available
        if ($request->header('CF-IPCountry')) {
            $document['geo'] = [
                'country' => $request->header('CF-IPCountry'),
                'city' => $request->header('CF-IPCity'),
            ];
        }

        $this->elasticsearch->indexDocument($this->indexName, $document);
    }

    /**
     * Sanitize headers to remove sensitive information.
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key', 'x-auth-token'];

        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['***REDACTED***'];
            }
        }

        return $headers;
    }

    /**
     * Create auth events index with proper mapping.
     */
    private function createAuthEventsIndex(): void
    {
        $mapping = [
            'mappings' => [
                'properties' => [
                    '@timestamp' => ['type' => 'date'],
                    'timestamp' => ['type' => 'date'],
                    'event_type' => ['type' => 'keyword'],
                    'user_id' => ['type' => 'long'],
                    'email' => ['type' => 'keyword'],
                    'ip_address' => ['type' => 'ip'],
                    'user_agent' => ['type' => 'text'],
                    'request_id' => ['type' => 'keyword'],
                    'session_id' => ['type' => 'keyword'],
                    'service' => ['type' => 'keyword'],
                    'environment' => ['type' => 'keyword'],
                    'context' => ['type' => 'object'],
                    'request_data' => [
                        'properties' => [
                            'method' => ['type' => 'keyword'],
                            'endpoint' => ['type' => 'keyword'],
                            'url' => ['type' => 'text'],
                        ],
                    ],
                    'geo' => [
                        'properties' => [
                            'country' => ['type' => 'keyword'],
                            'city' => ['type' => 'keyword'],
                        ],
                    ],
                ],
            ],
        ];

        $this->elasticsearch->createIndexIfNotExists($this->indexName, $mapping);
    }
}
