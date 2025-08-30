<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserRepository
 *
 * Concrete implementation of UserRepositoryInterface for Auth Service.
 * Handles all user-related database operations for authentication.
 */
final class UserRepository implements UserRepositoryInterface
{
    /**
     * Find user by ID.
     *
     * @param  int  $id  User ID
     * @return User|null User model or null if not found
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Find user by email address.
     *
     * @param  string  $email  Email address
     * @return User|null User model or null if not found
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create a new user during registration.
     *
     * @param  array<string, mixed>  $data  User registration data
     * @return User Created user model
     */
    public function create(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return User::create($data);
    }

    /**
     * Update user profile data.
     *
     * @param  int  $id  User ID
     * @param  array<string, mixed>  $data  Updated profile data
     * @return bool Update success status
     */
    public function update(int $id, array $data): bool
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return User::where('id', $id)->update($data);
    }

    /**
     * Delete user.
     *
     * @param  User  $user  User model
     * @return bool Deletion success
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Find user by verification token.
     *
     * @param  string  $token  Verification token
     * @return User|null User model or null if not found
     */
    public function findByVerificationToken(string $token): ?User
    {
        return User::where('email_verification_token', $token)->first();
    }

    /**
     * Find user by reset token.
     *
     * @param  string  $token  Reset token
     * @return User|null User model or null if not found
     */
    public function findByResetToken(string $token): ?User
    {
        return User::where('password_reset_token', $token)->first();
    }

    /**
     * Check if user exists by email.
     *
     * @param  string  $email  Email address
     * @return bool Existence status
     */
    public function existsByEmail(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    /**
     * Mark email as verified.
     *
     * @param  int  $id  User ID
     * @return bool Update success status
     */
    public function markEmailAsVerified(int $id): bool
    {
        return User::where('id', $id)->update([
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Update user password.
     *
     * @param  int  $id  User ID
     * @param  string  $hashedPassword  Hashed password
     * @return bool Update success status
     */
    public function updatePassword(int $id, string $hashedPassword): bool
    {
        return User::where('id', $id)->update([
            'password' => $hashedPassword,
        ]);
    }
}
