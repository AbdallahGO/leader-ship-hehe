<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * NotificationFactory - Generate test Notification instances
 * 
 * Creates notification records for testing notification management,
 * delivery, and user notification workflows.
 * 
 * @example
 * Notification::factory()->create();
 * Notification::factory()->count(10)->create();
 * Notification::factory()->unread()->for(User::factory())->create();
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        $titles = [
            'Welcome to Dashboard',
            'New Message',
            'Password Changed',
            'Login Successful',
            'Account Updated',
            'Security Alert',
            'System Maintenance',
            'New Comment',
            'File Uploaded',
            'Status Update',
        ];

        $messages = [
            'Your account has been updated successfully',
            'Someone just logged into your account',
            'Your password was changed',
            'Check your dashboard for updates',
            'A new notification for you',
            'Unusual activity detected',
            'System maintenance scheduled',
            'New comment on your post',
            'File uploaded successfully',
            'Your status has been updated',
        ];

        return [
            'user_id' => User::factory(),
            'title' => $this->faker->randomElement($titles),
            'message' => $this->faker->randomElement($messages),
            'is_read' => $this->faker->boolean(30), // 30% chance of being read
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * State: Unread notification
     */
    public function unread()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_read' => false,
            ];
        });
    }

    /**
     * State: Read notification
     */
    public function read()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_read' => true,
            ];
        });
    }

    /**
     * State: Recent notification (last hour)
     */
    public function recent()
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => now()->subMinutes($this->faker->numberBetween(0, 60)),
            ];
        });
    }

    /**
     * State: Old notification (30+ days ago)
     */
    public function old()
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => now()->subDays($this->faker->numberBetween(30, 365)),
            ];
        });
    }

    /**
     * State: Security-related notification
     */
    public function security()
    {
        return $this->state(function (array $attributes) {
            return [
                'title' => 'Security Alert',
                'message' => 'Suspicious activity detected on your account',
                'is_read' => false,
            ];
        });
    }
}
