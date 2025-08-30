<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\Auth\UserResource;
use App\Services\Contracts\AuthServiceInterface;
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
     * @param  AuthServiceInterface  $authService  Authentication service
     */
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    /**
     * Register a new user.
     *
     * @param  RegisterRequest  $request  Validated registration request
     * @return JsonResponse Registration response with user data and token
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        /** @var array{name: string, email: string, password: string} $validatedData */
        $validatedData = $request->validated();
        $result = $this->authService->register($validatedData);

        return $this->successResponse(
            data: new AuthResource($result),
            message: __('auth.registration_successful')
        );
    }

    /**
     * Login user and return authentication token.
     *
     * @param  LoginRequest  $request  Validated login request
     * @return JsonResponse Login response with user data and token
     *
     * @throws \App\Exceptions\Auth\InvalidCredentialsException When credentials are invalid
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
     * @param  Request  $request  Current request with authenticated user
     * @return JsonResponse Logout confirmation response
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        
        $this->authService->logout($user);

        return $this->successResponse(
            message: __('auth.logout_successful')
        );
    }

    /**
     * Refresh authentication token.
     *
     * @param  Request  $request  Current request with authenticated user
     * @return JsonResponse Refresh response with new token
     */
    public function refresh(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        
        $result = $this->authService->refreshToken($user);

        return $this->successResponse(
            data: new AuthResource($result),
            message: __('auth.token_refreshed')
        );
    }

    /**
     * Get authenticated user information.
     *
     * @param  Request  $request  Current request with authenticated user
     * @return JsonResponse User data response
     */
    public function me(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        
        return $this->successResponse(
            data: new UserResource($user)
        );
    }

    /**
     * Send password reset link to user email.
     *
     * @param  ForgotPasswordRequest  $request  Validated forgot password request
     * @return JsonResponse Password reset response
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->sendPasswordResetLink($request->input('email'));

        return $this->successResponse(
            message: __('auth.password_reset_sent')
        );
    }

    /**
     * Reset user password using reset token.
     *
     * @param  ResetPasswordRequest  $request  Validated reset password request
     * @return JsonResponse Password reset response
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        /** @var array{token: string, email: string, password: string, password_confirmation: string} $validatedData */
        $validatedData = $request->validated();
        $this->authService->resetPassword($validatedData);

        return $this->successResponse(
            message: __('auth.password_reset_successful')
        );
    }

    /**
     * Verify user email address.
     *
     * @param  VerifyEmailRequest  $request  Validated email verification request
     * @return JsonResponse Email verification response
     */
    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $result = $this->authService->verifyEmail(
            $request->input('id'),
            $request->input('hash')
        );

        $message = $result['already_verified']
            ? __('auth.email_already_verified')
            : __('auth.email_verified');

        return $this->successResponse(message: $message);
    }

    /**
     * Resend email verification notification.
     *
     * @param  Request  $request  Current request with authenticated user
     * @return JsonResponse Verification email response
     */
    public function resendVerification(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        
        $result = $this->authService->resendEmailVerification($user);

        $message = $result['already_verified']
            ? __('auth.email_already_verified')
            : __('auth.email_verification_sent');

        return $this->successResponse(message: $message);
    }
}
