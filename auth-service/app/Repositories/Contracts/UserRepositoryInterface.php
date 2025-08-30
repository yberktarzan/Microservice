<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

/**
 * Interface UserRepositoryInterface
 *
 * Repository contract for User model operations.
 * Defines data access layer methods for user management.
 */
interface UserRepositoryInterface
{
    /**
     * Find user by email.
     *
     * @param  string  $email  User email
     * @return User|null User model or null if not found
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $data  User data
     * @return User Created user model
     */
    public function create(array $data): User;

    /**
     * Find user by ID.
     *
     * @param  int|string  $id  User ID
     * @return User|null User model or null if not found
     */
    public function findById(int|string $id): ?User;

    /**
     * Update user data.
     *
     * @param  User  $user  User model
     * @param  array<string, mixed>  $data  Update data
     * @return User Updated user model
     */
    public function update(User $user, array $data): User;

    /**
     * Delete user.
     *
     * @param  User  $user  User model
     * @return bool Deletion success
     */
    public function delete(User $user): bool;

    /**
     * Find user by verification token.
     *
     * @param  string  $token  Verification token
     * @return User|null User model or null if not found
     */
    public function findByVerificationToken(string $token): ?User;

    /**
     * Find user by reset token.
     *
     * @param  string  $token  Reset token
     * @return User|null User model or null if not found
     */
    public function findByResetToken(string $token): ?User;
}
