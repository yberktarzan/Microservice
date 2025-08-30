<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Auth\EmailNotVerifiedException;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Exceptions\Auth\UserAlreadyExistsException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Exception handler for Auth microservice.
 *
 * Ensures all exceptions return JSON responses for API-only service.
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        // We handle all exceptions with custom reporting
    ];

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Exception reporting is handled in bootstrap/app.php
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     */
    public function render($request, Throwable $e): JsonResponse
    {
        // Handle validation exceptions
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => __('response.error.validation'),
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Handle custom auth exceptions
        if ($e instanceof UserAlreadyExistsException) {
            return response()->json([
                'success' => false,
                'message' => __('auth.user_already_exists'),
                'error_code' => 'USER_EXISTS',
            ], Response::HTTP_CONFLICT);
        }

        if ($e instanceof InvalidCredentialsException) {
            return response()->json([
                'success' => false,
                'message' => __('auth.invalid_credentials'),
                'error_code' => 'INVALID_CREDENTIALS',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($e instanceof EmailNotVerifiedException) {
            return response()->json([
                'success' => false,
                'message' => __('auth.email_not_verified'),
                'error_code' => 'EMAIL_NOT_VERIFIED',
            ], Response::HTTP_FORBIDDEN);
        }

        // Handle 404 Not Found
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => __('response.error.not_found'),
                'error_code' => 'NOT_FOUND',
            ], Response::HTTP_NOT_FOUND);
        }

        // Handle 405 Method Not Allowed
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed',
                'error_code' => 'METHOD_NOT_ALLOWED',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        // Handle other exceptions
        $statusCode = $this->getStatusCode($e);

        return response()->json([
            'success' => false,
            'message' => config('app.debug') ? $e->getMessage() : __('response.error.server_error'),
            'error_code' => 'SERVER_ERROR',
            'errors' => config('app.debug') ? ['exception' => $e->getMessage()] : null,
        ], $statusCode);
    }

    /**
     * Get HTTP status code from exception.
     */
    private function getStatusCode(Throwable $e): int
    {
        if (method_exists($e, 'getStatusCode')) {
            /** @var \Symfony\Component\HttpKernel\Exception\HttpException $e */
            return $e->getStatusCode();
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
