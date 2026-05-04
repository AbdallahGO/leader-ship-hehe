<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Login Attempt Tracking Service
 * 
 * Tracks failed login attempts and implements account lockout
 * Max 5 failed attempts within 15 minutes results in lockout
 */
class LoginAttemptService
{
    /**
     * Maximum failed attempts before lockout
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Lockout duration in minutes
     */
    private const LOCKOUT_DURATION = 15;

    /**
     * Get cache key for login attempts
     */
    private function getAttemptsKey(string $email): string
    {
        return "login_attempts:{$email}";
    }

    /**
     * Get cache key for lockout status
     */
    private function getLockoutKey(string $email): string
    {
        return "login_lockout:{$email}";
    }

    /**
     * Record a failed login attempt
     */
    public function recordFailedAttempt(string $email): void
    {
        $key = $this->getAttemptsKey($email);
        $attempts = Cache::get($key, 0);
        
        Cache::put($key, $attempts + 1, now()->addMinutes(self::LOCKOUT_DURATION));

        // Lock account if max attempts exceeded
        if ($attempts + 1 >= self::MAX_ATTEMPTS) {
            Cache::put(
                $this->getLockoutKey($email),
                true,
                now()->addMinutes(self::LOCKOUT_DURATION)
            );
        }
    }

    /**
     * Clear failed login attempts (on successful login)
     */
    public function clearAttempts(string $email): void
    {
        Cache::forget($this->getAttemptsKey($email));
        Cache::forget($this->getLockoutKey($email));
    }

    /**
     * Check if account is locked
     */
    public function isAccountLocked(string $email): bool
    {
        return Cache::has($this->getLockoutKey($email));
    }

    /**
     * Get remaining attempts before lockout
     */
    public function getRemainingAttempts(string $email): int
    {
        $attempts = Cache::get($this->getAttemptsKey($email), 0);
        return max(0, self::MAX_ATTEMPTS - $attempts);
    }

    /**
     * Get lockout duration in minutes
     */
    public function getLockoutDuration(): int
    {
        return self::LOCKOUT_DURATION;
    }

    /**
     * Get max attempts allowed
     */
    public function getMaxAttempts(): int
    {
        return self::MAX_ATTEMPTS;
    }
}
