<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\User;

/**
 * Interface UserRepositoryInterface
 *
 * Repository contract for User entity operations in Auth Service.
 * Focuses on authentication-related user operations only.
 */
interface UserRepositoryInterface
{
    /**
     * Find user by ID.
     *
     * @param int $id User ID
     * @return User|null User model or null if not found
     */
    public function findById(int $id): ?User;

    /**
     * Find user by email address.
     *
     * @param string $email Email address
     * @return User|null User model or null if not found
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user during registration.
     *
     * @param array<string, mixed> $data User registration data
     * @return User Created user model
     */
    public function create(array $data): User;

    /**
     * Update user profile data.
     *
     * @param int $id User ID
     * @param array<string, mixed> $data Updated profile data
     * @return bool Update success status
     */
    public function update(int $id, array $data): bool;

    /**
     * Check if user exists by email.
     *
     * @param string $email Email address
     * @return bool Existence status
     */
    public function existsByEmail(string $email): bool;

    /**
     * Mark email as verified.
     *
     * @param int $id User ID
     * @return bool Update success status
     */
    public function markEmailAsVerified(int $id): bool;

    /**
     * Update user password.
     *
     * @param int $id User ID
     * @param string $hashedPassword Hashed password
     * @return bool Update success status
     */
    public function updatePassword(int $id, string $hashedPassword): bool;
}
