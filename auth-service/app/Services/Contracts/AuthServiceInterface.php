<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\User;

/**
 * Interface AuthServiceInterface
 *
 * Service contract for authentication operations in Auth microservice.
 * Defines the core authentication business logic methods.
 */
interface AuthServiceInterface
{
    /**
     * Register a new user.
     *
     * @param  array{name: string, email: string, password: string}  $data  Registration data
     * @return array{user: User, token: string} User and token data
     */
    public function register(array $data): array;

    /**
     * Login user with email and password.
     *
     * @param  string  $email  User email
     * @param  string  $password  User password
     * @return array{user: User, token: string} User and token data
     *
     * @throws \App\Exceptions\Auth\InvalidCredentialsException
     */
    public function login(string $email, string $password): array;

    /**
     * Logout user and revoke current token.
     *
     * @param  User  $user  Authenticated user
     */
    public function logout(User $user): void;

    /**
     * Refresh user authentication token.
     *
     * @param  User  $user  Authenticated user
     * @return array{token: string} User and new token data
     */
    public function refreshToken(User $user): array;

    /**
     * Send password reset link to user email.
     *
     * @param  string  $email  User email
     *
     * @throws \Illuminate\Validation\ValidationException When email sending fails
     */
    public function sendPasswordResetLink(string $email): void;

    /**
     * Reset user password using reset token.
     *
     * @param  array{token: string, email: string, password: string, password_confirmation: string}  $data  Password reset data (token, email, password, password_confirmation)
     *
     * @throws \Illuminate\Validation\ValidationException When reset fails
     */
    public function resetPassword(array $data): void;

    /**
     * Verify user email address.
     *
     * @param  int  $userId  User ID
     * @param  string  $hash  Verification hash
     * @return array{already_verified: bool} Verification result
     *
     * @throws \Illuminate\Validation\ValidationException When verification fails
     */
    public function verifyEmail(int $userId, string $hash): array;

    /**
     * Resend email verification notification.
     *
     * @param  User  $user  Authenticated user
     * @return array{already_verified: bool} Resend result
     */
    public function resendEmailVerification(User $user): array;
}
