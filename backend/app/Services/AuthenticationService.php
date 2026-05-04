<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

/**
 * Authentication Service
 * 
 * Handles all authentication-related business logic.
 * Encapsulates registration, login, logout, and password management.
 */
class AuthenticationService
{
    public function __construct(protected UserRepository $userRepository)
    {
    }

    /**
     * Register a new user
     * 
     * @throws \Exception
     */
    public function register(array $data): User
    {
        // Check if email already exists
        if ($this->userRepository->emailExists($data['email'])) {
            throw new \Exception('Email already registered');
        }

        // Create the user
        return $this->userRepository->create($data);
    }

    /**
     * Authenticate user and return token
     * 
     * @throws \Exception
     */
    public function login(string $email, string $password): array
    {
        // Find user by email
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Invalid credentials');
        }

        // Check if email is verified
        if ($user->email_verified_at === null) {
            throw new \Exception('Email not verified');
        }

        // Generate token
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
            'expires_at' => now()->addHours(24)->toIso8601String(),
        ];
    }

    /**
     * Logout user by revoking all tokens
     */
    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Verify user email
     */
    public function verifyEmail(User $user): bool
    {
        return $this->userRepository->update($user, [
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Change user password
     * 
     * @throws \Exception
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        // Verify current password
        if (!Hash::check($currentPassword, $user->password)) {
            throw new \Exception('Current password is incorrect');
        }

        return $this->userRepository->update($user, [
            'password' => Hash::make($newPassword),
        ]);
    }

    /**
     * Reset password without current password verification
     */
    public function resetPassword(User $user, string $newPassword): bool
    {
        return $this->userRepository->update($user, [
            'password' => Hash::make($newPassword),
        ]);
    }

    /**
     * Get authenticated user
     */
    public function getAuthenticatedUser(): ?User
    {
        return Auth::user();
    }
}
