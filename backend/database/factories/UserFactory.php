<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * UserFactory - Generate test User instances
 * 
 * Creates realistic test users with various states for testing
 * authentication, authorization, and user-related functionality.
 * 
 * @example
 * User::factory()->create();
 * User::factory()->count(10)->create();
 * User::factory()->admin()->create();
 * User::factory()->banned()->create();
 * User::factory()->suspended()->create();
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'remember_token' => Str::random(10),
            'is_banned' => false,
            'is_suspended' => false,
            'banned_at' => null,
            'suspended_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * State: Unverified user
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    /**
     * State: Banned user
     */
    public function banned()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_banned' => true,
                'banned_at' => now(),
            ];
        });
    }

    /**
     * State: Suspended user
     */
    public function suspended()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_suspended' => true,
                'suspended_at' => now(),
            ];
        });
    }

    /**
     * State: Admin user
     */
    public function admin()
    {
        return $this->afterCreating(function (User $user) {
            $adminRole = \App\Models\Role::where('name', 'Administrator')->first();
            if ($adminRole) {
                $user->roles()->attach($adminRole);
            }
        });
    }

    /**
     * State: Moderator user
     */
    public function moderator()
    {
        return $this->afterCreating(function (User $user) {
            $moderatorRole = \App\Models\Role::where('name', 'Moderator')->first();
            if ($moderatorRole) {
                $user->roles()->attach($moderatorRole);
            }
        });
    }
}
