<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Exceptions\Auth\EmailNotVerifiedException;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Exceptions\Auth\UserAlreadyExistsException;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * Authentication service implementation.
 *
 * Handles all authentication-related business logic.
 */
class AuthService implements AuthServiceInterface
{
    /**
     * Create a new authentication service instance.
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Register a new user.
     *
     * @param  array  $data  User registration data
     * @return array User data with token
     *
     * @throws UserAlreadyExistsException
     */
    public function register(array $data): array
    {
        // Check if user already exists
        if ($this->userRepository->findByEmail($data['email'])) {
            throw new UserAlreadyExistsException;
        }

        // Create user
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Fire registered event
        event(new Registered($user));

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Login user with email and password.
     *
     * @param  string  $email  User email
     * @param  string  $password  User password
     * @return array User data with token
     *
     * @throws InvalidCredentialsException|EmailNotVerifiedException
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new InvalidCredentialsException;
        }

        if (! $user->hasVerifiedEmail()) {
            throw new EmailNotVerifiedException;
        }

        // Revoke all existing tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout user.
     */
    public function logout(\App\Models\User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Refresh user token.
     *
     * @return array New token data
     */
    public function refreshToken(\App\Models\User $user): array
    {
        // Revoke current token
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'token' => $token,
        ];
    }

    /**
     * Send password reset link.
     *
     * @throws ValidationException
     */
    public function sendPasswordResetLink(string $email): void
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    /**
     * Reset password.
     *
     * @param  array  $data  Password reset data
     *
     * @throws ValidationException
     */
    public function resetPassword(array $data): void
    {
        $status = Password::reset(
            $data,
            function ($user) use ($data) {
                $user->forceFill([
                    'password' => Hash::make($data['password']),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    /**
     * Send email verification notification.
     */
    public function sendEmailVerification(\App\Models\User $user): void
    {
        $user->sendEmailVerificationNotification();
    }

    /**
     * Verify email.
     *
     * @param  int  $userId  User ID
     * @param  string  $hash  Verification hash
     * @return array Verification result
     */
    public function verifyEmail(int $userId, string $hash): array
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new ValidationException('User not found');
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            throw new ValidationException('Invalid verification hash');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return ['message' => 'Email verified successfully'];
    }

    /**
     * Resend email verification notification.
     *
     * @return array Resend result
     */
    public function resendEmailVerification(\App\Models\User $user): array
    {
        if ($user->hasVerifiedEmail()) {
            return ['message' => 'Email is already verified'];
        }

        $user->sendEmailVerificationNotification();

        return ['message' => 'Verification email sent successfully'];
    }
}
