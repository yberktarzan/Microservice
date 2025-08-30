<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Authentication exception.
 *
 * Base exception for authentication-related errors.
 */
class AuthException extends Exception
{
    /**
     * Create a new authentication exception instance.
     *
     * @param string $message Exception message
     * @param int $code HTTP status code
     */
    public function __construct(string $message = 'Authentication failed', int $code = 401)
    {
        parent::__construct($message, $code);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => 'AUTH_FAILED',
        ], $this->getCode());
    }
}
