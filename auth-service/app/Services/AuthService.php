<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Services\Contracts\AuthServiceInterface;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Class AuthService
 *
 * Service implementation for authentication operations.
 * Contains all business logic for user authentication.
 */
final class AuthService implements AuthServiceInterface
{
    use ApiResponse;

    /**
     * Constructor.
     *
     * @param UserRepositoryInterface $userRepository User repository
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Register a new user.
     *
     * @param array<string, mixed> $data Registration data
     * @return JsonResponse Registration response
     * @throws ValidationException
     */
    public function register(array $data): JsonResponse
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'email_verified_at' => null,
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse(
            [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
            'User registered successfully',
            201
        );
    }

    /**
     * Authenticate user login.
     *
     * @param array<string, mixed> $credentials Login credentials
     * @return JsonResponse Login response
     * @throws ValidationException
     */
    public function login(array $credentials): JsonResponse
    {
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        if (!Auth::attempt($credentials)) {
            return $this->unauthorizedResponse('Invalid credentials');
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse(
            [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
            'Login successful'
        );
    }

    /**
     * Logout user and revoke current token.
     *
     * @param User $user Authenticated user
     * @return JsonResponse Logout response
     */
    public function logout(User $user): JsonResponse
    {
        $user->tokens()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Refresh user token.
     *
     * @param User $user Authenticated user
     * @return JsonResponse Refresh response
     */
    public function refreshToken(User $user): JsonResponse
    {
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse(
            [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
            'Token refreshed successfully'
        );
    }

    /**
     * Get authenticated user profile.
     *
     * @param User $user Authenticated user
     * @return JsonResponse User profile response
     */
    public function getProfile(User $user): JsonResponse
    {
        return $this->successResponse(
            ['user' => $user],
            'User profile retrieved successfully'
        );
    }

    /**
     * Send password reset link.
     *
     * @param string $email User email
     * @return JsonResponse Password reset response
     * @throws ValidationException
     */
    public function sendPasswordResetLink(string $email): JsonResponse
    {
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $status = Password::sendResetLink(['email' => $email]);

        if ($status === Password::RESET_LINK_SENT) {
            return $this->successResponse(null, 'Password reset link sent to your email');
        }

        return $this->errorResponse('Unable to send password reset link', 500);
    }

    /**
     * Reset user password.
     *
     * @param array<string, mixed> $data Password reset data
     * @return JsonResponse Password reset response
     * @throws ValidationException
     */
    public function resetPassword(array $data): JsonResponse
    {
        $validator = Validator::make($data, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $status = Password::reset(
            $data,
            function (User $user, string $password): void {
                $this->userRepository->updatePassword(
                    $user->id,
                    Hash::make($password)
                );
                $user->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->successResponse(null, 'Password reset successfully');
        }

        return $this->errorResponse('Unable to reset password', 500);
    }

    /**
     * Verify user email.
     *
     * @param int $userId User ID
     * @param string $hash Verification hash
     * @return JsonResponse Email verification response
     */
    public function verifyEmail(int $userId, string $hash): JsonResponse
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            return $this->notFoundResponse('User not found');
        }

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return $this->badRequestResponse('Invalid verification link');
        }

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(null, 'Email already verified');
        }

        if ($this->userRepository->markEmailAsVerified($userId)) {
            event(new Verified($user));
        }

        return $this->successResponse(null, 'Email verified successfully');
    }

    /**
     * Resend email verification notification.
     *
     * @param User $user Authenticated user
     * @return JsonResponse Resend verification response
     */
    public function resendEmailVerification(User $user): JsonResponse
    {
        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(null, 'Email already verified');
        }

        $user->sendEmailVerificationNotification();

        return $this->successResponse(null, 'Verification email sent');
    }
}
