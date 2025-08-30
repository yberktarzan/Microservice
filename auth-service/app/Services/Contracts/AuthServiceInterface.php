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
     * @param array<string, mixed> $data Registration data
     * @return array<string, mixed> User and token data
     */
    public function register(array $data): array;

    /**
     * Login user with email and password.
     *
     * @param string $email User email
     * @param string $password User password
     * @return array<string, mixed> User and token data
     * @throws \App\Exceptions\Auth\InvalidCredentialsException
     */
    public function login(string $email, string $password): array;

    /**
     * Logout user and revoke current token.
     *
     * @param User $user Authenticated user
     * @return void
     */
    public function logout(User $user): void;

    /**
     * Refresh user authentication token.
     *
     * @param User $user Authenticated user
     * @return array<string, mixed> User and new token data
     */
    public function refreshToken(User $user): array;

    /**
     * Send password reset link to user email.
     *
     * @param string $email User email
     * @return void
     * @throws \Exception When email sending fails
     */
    public function sendPasswordResetLink(string $email): void;

    /**
     * Reset user password using reset token.
     *
     * @param array<string, mixed> $data Password reset data (token, email, password, password_confirmation)
     * @return void
     * @throws \Exception When reset fails
     */
    public function resetPassword(array $data): void;

    /**
     * Verify user email address.
     *
     * @param int $userId User ID
     * @param string $hash Verification hash
     * @return array<string, mixed> Verification result
     * @throws \Exception When verification fails
     */
    public function verifyEmail(int $userId, string $hash): array;

    /**
     * Resend email verification notification.
     *
     * @param User $user Authenticated user
     * @return array<string, mixed> Resend result
     * @throws \Exception When sending fails
     */
    public function resendEmailVerification(User $user): array;
}
