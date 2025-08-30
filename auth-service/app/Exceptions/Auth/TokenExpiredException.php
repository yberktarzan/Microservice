<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Illuminate\Http\JsonResponse;

/**
 * Token expired exception.
 *
 * Thrown when authentication token has expired.
 */
class TokenExpiredException extends AuthException
{
    /**
     * Create a new token expired exception instance.
     */
    public function __construct()
    {
        parent::__construct(__('auth.token_expired'), 401);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => 'TOKEN_EXPIRED',
        ], $this->getCode());
    }
}
