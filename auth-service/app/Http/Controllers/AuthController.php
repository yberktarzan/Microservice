<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\Auth\UserResource;
use App\Services\Contracts\AuthServiceInterface;
use App\Exceptions\Auth\InvalidCredentialsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Authentication controller for Auth microservice.
 *
 * Handles all authentication-related operations including registration,
 * login, logout, token management, password reset, and email verification.
 */
class AuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @param AuthServiceInterface $authService Authentication service
     */
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    /**
     * Register a new user.
     *
     * @param RegisterRequest $request Validated registration request
     * @return JsonResponse Registration response with user data and token
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());
            
            return $this->successResponse(
                data: new AuthResource($result),
                message: __('auth.registration_successful')
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: __('auth.registration_failed'),
                status: 422
            );
        }
    }

    /**
     * Login user and return authentication token.
     *
     * @param LoginRequest $request Validated login request
     * @return JsonResponse Login response with user data and token
     * @throws InvalidCredentialsException When credentials are invalid
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->input('email'),
            $request->input('password')
        );

        return $this->successResponse(
            data: new AuthResource($result),
            message: __('auth.login_successful')
        );
    }

    /**
     * Logout user and revoke current token.
     *
     * @param Request $request Current request with authenticated user
     * @return JsonResponse Logout confirmation response
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse(
            message: __('auth.logout_successful')
        );
    }

    /**
     * Refresh authentication token.
     *
     * @param Request $request Current request with authenticated user
     * @return JsonResponse Refresh response with new token
     */
    public function refresh(Request $request): JsonResponse
    {
        $result = $this->authService->refreshToken($request->user());

        return $this->successResponse(
            data: new AuthResource($result),
            message: __('auth.token_refreshed')
        );
    }

    /**
     * Get authenticated user information.
     *
     * @param Request $request Current request with authenticated user
     * @return JsonResponse User data response
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            data: new UserResource($request->user())
        );
    }

    /**
     * Send password reset link to user email.
     *
     * @param ForgotPasswordRequest $request Validated forgot password request
     * @return JsonResponse Password reset response
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->sendPasswordResetLink($request->input('email'));

            return $this->successResponse(
                message: __('auth.password_reset_sent')
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: __('auth.password_reset_failed'),
                status: 500
            );
        }
    }

    /**
     * Reset user password using reset token.
     *
     * @param ResetPasswordRequest $request Validated reset password request
     * @return JsonResponse Password reset response
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->resetPassword($request->validated());

            return $this->successResponse(
                message: __('auth.password_reset_successful')
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: __('auth.password_reset_invalid'),
                status: 400
            );
        }
    }

    /**
     * Verify user email address.
     *
     * @param VerifyEmailRequest $request Validated email verification request
     * @return JsonResponse Email verification response
     */
    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->verifyEmail(
                $request->input('id'),
                $request->input('hash')
            );

            $message = $result['already_verified'] 
                ? __('auth.email_already_verified')
                : __('auth.email_verified');

            return $this->successResponse(message: $message);
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: __('auth.email_verification_invalid'),
                status: 400
            );
        }
    }

    /**
     * Resend email verification notification.
     *
     * @param Request $request Current request with authenticated user
     * @return JsonResponse Verification email response
     */
    public function resendVerification(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->resendEmailVerification($request->user());

            $message = $result['already_verified']
                ? __('auth.email_already_verified')
                : __('auth.email_verification_sent');

            return $this->successResponse(message: $message);
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: __('auth.email_verification_failed'),
                status: 500
            );
        }
    }
}
