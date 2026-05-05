<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * RoleFactory - Generate test Role instances
 * 
 * Creates various role types for testing role-based access control (RBAC)
 * and role assignment functionality.
 * 
 * @example
 * Role::factory()->create();
 * Role::factory()->admin()->create();
 * Role::factory()->moderator()->create();
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $names = ['Administrator', 'Moderator', 'User', 'Contributor', 'Reviewer'];

        return [
            'name' => $this->faker->unique()->randomElement($names),
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * State: Administrator role
     */
    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Administrator',
                'description' => 'Full system access',
                'is_active' => true,
            ];
        });
    }

    /**
     * State: Moderator role
     */
    public function moderator()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Moderator',
                'description' => 'Content moderation access',
                'is_active' => true,
            ];
        });
    }

    /**
     * State: User role
     */
    public function user()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'User',
                'description' => 'Standard user access',
                'is_active' => true,
            ];
        });
    }

    /**
     * State: Inactive role
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
