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

        return User::where('id', $id)->update($data) > 0;
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
        ]) > 0;
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
        ]) > 0;
    }
}
