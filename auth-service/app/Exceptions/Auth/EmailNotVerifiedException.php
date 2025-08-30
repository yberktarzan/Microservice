<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Illuminate\Http\JsonResponse;

/**
 * Email not verified exception.
 *
 * Thrown when user tries to access protected resources without email verification.
 */
class EmailNotVerifiedException extends AuthException
{
    /**
     * Create a new email not verified exception instance.
     */
    public function __construct()
    {
        parent::__construct(__('auth.account_not_verified'), 403);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => 'EMAIL_NOT_VERIFIED',
        ], $this->getCode());
    }
}
