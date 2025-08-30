<?php

declare(strict_types=1);

namespace App\Services\Logging;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Service for logging API responses with contextual information.
 *
 * Handles different log levels based on HTTP status codes and provides
 * structured logging for monitoring and debugging purposes.
 */
class ApiResponseLogger
{
    /**
     * Log an API response with contextual information.
     *
     * @param array{
     *     success: bool,
     *     message: string|null,
     *     status_code: int,
     *     errors?: array<string, mixed>,
     *     data?: mixed
     * } $responseData
     */
    public function log(array $responseData): void
    {
        $context = $this->buildLogContext($responseData);

        if ($responseData['success']) {
            $this->logSuccessResponse($context);
        } else {
            $this->logErrorResponse($responseData['status_code'], $context);
        }
    }

    /**
     * Build comprehensive log context with request and user information.
     *
     * @param array{
     *     success: bool,
     *     message: string|null,
     *     status_code: int,
     *     errors?: array<string, mixed>,
     *     data?: mixed
     * } $responseData
     * @return array<string, mixed>
     */
    private function buildLogContext(array $responseData): array
    {
        if (app()->runningInConsole()) {
            return [
                'success' => $responseData['success'],
                'status_code' => $responseData['status_code'],
                'message' => $responseData['message'],
                'timestamp' => now()->toISOString(),
                'context' => 'console',
            ];
        }

        $request = request();

        $context = [
            'success' => $responseData['success'],
            'status_code' => $responseData['status_code'],
            'message' => $responseData['message'],
            'timestamp' => now()->toISOString(),
            'request' => [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'endpoint' => $request->path(),
            ],
        ];

        $authId = $request->header('authid');
        $companyId = $request->header('company-id');

        if ($authId || $companyId) {
            $context['gateway'] = [
                'auth_user_id' => $authId,
                'company_id' => $companyId,
            ];
        }

        if (Auth::check()) {
            $user = Auth::user();
            if ($user !== null) {
                /** @var object $user */
                $userId = property_exists($user, 'id') ? $user->id : null;
                $userEmail = property_exists($user, 'email') ? $user->email : null;
                $context['user'] = [
                    'id' => $userId,
                    'email' => is_string($userEmail) ? $userEmail : null,
                ];
            }
        }

        if (! $responseData['success'] && ! empty($responseData['errors'])) {
            $context['errors'] = $responseData['errors'];
        }

        if ($responseData['success'] && isset($responseData['data'])) {
            $context['data_type'] = gettype($responseData['data']);
            if (is_array($responseData['data']) || is_countable($responseData['data'])) {
                $context['data_count'] = count($responseData['data']);
            }
        }

        return $context;
    }

    /**
     * Log successful API responses.
     *
     * @param  array<string, mixed>  $context
     */
    private function logSuccessResponse(array $context): void
    {
        Log::info('API Success Response', $context);
    }

    /**
     * Log error responses with appropriate log levels based on status code.
     *
     * @param  array<string, mixed>  $context
     */
    private function logErrorResponse(int $statusCode, array $context): void
    {
        $logMessage = $this->getLogMessageForStatusCode($statusCode);

        match (true) {
            $statusCode >= 500 => Log::error($logMessage, $context),
            in_array($statusCode, [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN]) => Log::warning($logMessage, $context),
            in_array($statusCode, [Response::HTTP_UNPROCESSABLE_ENTITY]) => Log::warning($logMessage, $context),
            $statusCode >= 400 => Log::info($logMessage, $context),
            default => Log::warning('API Unknown Error Response', $context),
        };
    }

    /**
     * Get appropriate log message based on HTTP status code.
     */
    private function getLogMessageForStatusCode(int $statusCode): string
    {
        return match ($statusCode) {
            Response::HTTP_BAD_REQUEST => 'API Bad Request',
            Response::HTTP_UNAUTHORIZED => 'API Unauthorized Access',
            Response::HTTP_FORBIDDEN => 'API Forbidden Access',
            Response::HTTP_NOT_FOUND => 'API Resource Not Found',
            Response::HTTP_METHOD_NOT_ALLOWED => 'API Method Not Allowed',
            Response::HTTP_UNPROCESSABLE_ENTITY => 'API Validation Error',
            Response::HTTP_TOO_MANY_REQUESTS => 'API Rate Limit Exceeded',
            Response::HTTP_INTERNAL_SERVER_ERROR => 'API Internal Server Error',
            Response::HTTP_BAD_GATEWAY => 'API Bad Gateway',
            Response::HTTP_SERVICE_UNAVAILABLE => 'API Service Unavailable',
            Response::HTTP_GATEWAY_TIMEOUT => 'API Gateway Timeout',
            default => "API Error Response (HTTP {$statusCode})",
        };
    }
}
