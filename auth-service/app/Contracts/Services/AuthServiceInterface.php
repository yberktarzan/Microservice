<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Interface AuthServiceInterface
 *
 * Service contract for authentication operations.
 * Defines business logic for user authentication and authorization.
 */
interface AuthServiceInterface
{
    /**
     * Register a new user.
     *
     * @param  array<string, mixed>  $data  Registration data
     * @return JsonResponse Registration response
     */
    public function register(array $data): JsonResponse;

    /**
     * Authenticate user login.
     *
     * @param  array<string, mixed>  $credentials  Login credentials
     * @return JsonResponse Login response
     */
    public function login(array $credentials): JsonResponse;

    /**
     * Logout authenticated user.
     *
     * @param  User  $user  Authenticated user
     * @return JsonResponse Logout response
     */
    public function logout(User $user): JsonResponse;

    /**
     * Refresh user authentication token.
     *
     * @param  User  $user  Authenticated user
     * @return JsonResponse Token refresh response
     */
    public function refreshToken(User $user): JsonResponse;

    /**
     * Get authenticated user information.
     *
     * @param  User  $user  Authenticated user
     * @return JsonResponse User information response
     */
    public function getAuthenticatedUser(User $user): JsonResponse;

    /**
     * Send password reset link.
     *
     * @param  string  $email  User email
     * @return JsonResponse Password reset response
     */
    public function sendPasswordResetLink(string $email): JsonResponse;

    /**
     * Reset user password.
     *
     * @param  array<string, mixed>  $data  Reset password data
     * @return JsonResponse Password reset response
     */
    public function resetPassword(array $data): JsonResponse;

    /**
     * Verify user email.
     *
     * @param  int  $id  User ID
     * @param  string  $hash  Verification hash
     * @return JsonResponse Email verification response
     */
    public function verifyEmail(int $id, string $hash): JsonResponse;

    /**
     * Resend email verification notification.
     *
     * @param  User  $user  User to send verification
     * @return JsonResponse Verification resend response
     */
    public function resendEmailVerification(User $user): JsonResponse;
}
