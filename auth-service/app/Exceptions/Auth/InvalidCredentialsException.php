<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Illuminate\Http\JsonResponse;

/**
 * Invalid credentials exception.
 *
 * Thrown when user provides invalid login credentials.
 */
class InvalidCredentialsException extends AuthException
{
    /**
     * Create a new invalid credentials exception instance.
     */
    public function __construct()
    {
        parent::__construct(__('auth.login_failed'), 401);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => 'INVALID_CREDENTIALS',
        ], $this->getCode());
    }
}
