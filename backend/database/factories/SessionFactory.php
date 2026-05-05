<?php

namespace Database\Factories;

use App\Models\Session;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * SessionFactory - Generate test Session instances
 * 
 * Creates session records for testing session management,
 * login tracking, and concurrent session functionality.
 * 
 * @example
 * Session::factory()->create();
 * Session::factory()->count(5)->for(User::factory())->create();
 */
class SessionFactory extends Factory
{
    protected $model = Session::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ip_address' => $this->faker->ipv4(),
            'device' => $this->faker->userAgent(),
            'last_activity' => now(),
            'created_at' => now(),
        ];
    }

    /**
     * State: Active session
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'last_activity' => now(),
            ];
        });
    }

    /**
     * State: Inactive session (last activity 30+ days ago)
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'last_activity' => now()->subDays(30),
            ];
        });
    }

    /**
     * State: Mobile device session
     */
    public function mobile()
    {
        return $this->state(function (array $attributes) {
            return [
                'device' => $this->faker->mobile(),
            ];
        });
    }

    /**
     * State: Desktop device session
     */
    public function desktop()
    {
        return $this->state(function (array $attributes) {
            return [
                'device' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ];
        });
    }
}
