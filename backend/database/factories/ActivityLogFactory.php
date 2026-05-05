<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ActivityLogFactory - Generate test ActivityLog instances
 * 
 * Creates activity log records for testing audit trails,
 * user action tracking, and activity reporting.
 * 
 * @example
 * ActivityLog::factory()->create();
 * ActivityLog::factory()->count(100)->create();
 * ActivityLog::factory()->login()->for(User::factory())->create();
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        $actions = [
            'login',
            'logout',
            'profile_updated',
            'password_changed',
            'avatar_uploaded',
            'post_created',
            'post_updated',
            'post_deleted',
            'comment_posted',
            'file_uploaded',
            'file_deleted',
            'role_assigned',
            'permission_granted',
            'user_banned',
            'user_suspended',
            'setting_changed',
            'notification_sent',
            'failed_login_attempt',
            'api_call',
            'export_initiated',
        ];

        return [
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement($actions),
            'created_at' => now()->subDays($this->faker->numberBetween(0, 90)),
        ];
    }

    /**
     * State: Login action
     */
    public function login()
    {
        return $this->state(function (array $attributes) {
            return [
                'action' => 'login',
            ];
        });
    }

    /**
     * State: Logout action
     */
    public function logout()
    {
        return $this->state(function (array $attributes) {
            return [
                'action' => 'logout',
            ];
        });
    }

    /**
     * State: Profile updated action
     */
    public function profileUpdated()
    {
        return $this->state(function (array $attributes) {
            return [
                'action' => 'profile_updated',
            ];
        });
    }

    /**
     * State: Password changed action
     */
    public function passwordChanged()
    {
        return $this->state(function (array $attributes) {
            return [
                'action' => 'password_changed',
            ];
        });
    }

    /**
     * State: Failed login action
     */
    public function failedLogin()
    {
        return $this->state(function (array $attributes) {
            return [
                'action' => 'failed_login_attempt',
            ];
        });
    }

    /**
     * State: File operation action
     */
    public function fileOperation()
    {
        return $this->state(function (array $attributes) {
            $fileActions = ['file_uploaded', 'file_deleted'];
            return [
                'action' => $this->faker->randomElement($fileActions),
            ];
        });
    }

    /**
     * State: User management action
     */
    public function userManagement()
    {
        return $this->state(function (array $attributes) {
            $mgmtActions = ['role_assigned', 'permission_granted', 'user_banned', 'user_suspended'];
            return [
                'action' => $this->faker->randomElement($mgmtActions),
            ];
        });
    }

    /**
     * State: Recent activity (last 24 hours)
     */
    public function recent()
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => now()->subHours($this->faker->numberBetween(0, 24)),
            ];
        });
    }

    /**
     * State: Old activity (30+ days ago)
     */
    public function old()
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => now()->subDays($this->faker->numberBetween(30, 365)),
            ];
        });
    }
}
