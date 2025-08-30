<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * User already exists exception.
 *
 * Thrown when attempting to register a user that already exists.
 */
class UserAlreadyExistsException extends AuthException
{
    /**
     * Create a new user already exists exception instance.
     */
    public function __construct()
    {
        parent::__construct(__('auth.user_already_exists'), Response::HTTP_CONFLICT);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => 'USER_EXISTS',
        ], $this->getCode());
    }
}
