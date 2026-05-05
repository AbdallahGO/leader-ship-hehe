<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * PermissionFactory - Generate test Permission instances
 * 
 * Creates permission objects for testing permission-based access control
 * and permission assignment functionality.
 * 
 * @example
 * Permission::factory()->create();
 * Permission::factory()->count(5)->create();
 * Permission::factory()->admin()->create();
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $actions = ['create', 'read', 'update', 'delete', 'publish', 'manage'];
        $resources = ['users', 'roles', 'permissions', 'posts', 'comments', 'settings'];
        $categories = ['User Management', 'Content Management', 'System Administration', 'Analytics', 'Security', 'Reports'];

        $action = $this->faker->randomElement($actions);
        $resource = $this->faker->randomElement($resources);

        return [
            'name' => "{$action}-{$resource}",
            'description' => ucfirst($action) . " " . $resource,
            'category' => $this->faker->randomElement($categories),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * State: Create permission
     */
    public function create()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'create-' . $this->faker->word(),
                'description' => 'Create resources',
            ];
        });
    }

    /**
     * State: Read permission
     */
    public function read()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'read-' . $this->faker->word(),
                'description' => 'View resources',
            ];
        });
    }

    /**
     * State: Update permission
     */
    public function update()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'update-' . $this->faker->word(),
                'description' => 'Modify resources',
            ];
        });
    }

    /**
     * State: Delete permission
     */
    public function delete()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'delete-' . $this->faker->word(),
                'description' => 'Remove resources',
            ];
        });
    }

    /**
     * State: Inactive permission
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
