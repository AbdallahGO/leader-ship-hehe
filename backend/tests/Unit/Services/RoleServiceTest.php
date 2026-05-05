<?php

namespace Tests\Unit\Services;

use App\Models\Role;
use App\Models\Permission;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RoleService Test
 *
 * Tests for RoleService functionality including role management
 * and permission assignment operations.
 */
class RoleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RoleService $roleService;
    protected Role $role;
    protected Permission $permission;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->roleService = app(RoleService::class);

        // Create test permission
        $this->permission = Permission::create([
            'name' => 'View Posts',
            'slug' => 'posts.view',
            'category' => 'posts',
            'is_active' => true,
        ]);
    }

    /**
     * Test: Service can create role
     */
    public function test_service_can_create_role(): void
    {
        $role = $this->roleService->create([
            'name' => 'Editor',
            'description' => 'Content editor role',
            'is_active' => true,
        ]);

        $this->assertIsNotNull($role->id);
        $this->assertEquals('Editor', $role->name);
        $this->assertEquals('editor', $role->slug);
    }

    /**
     * Test: Service generates slug automatically
     */
    public function test_service_generates_slug_automatically(): void
    {
        $role = $this->roleService->create([
            'name' => 'Content Manager',
            'is_active' => true,
        ]);

        $this->assertEquals('content-manager', $role->slug);
    }

    /**
     * Test: Service can update role
     */
    public function test_service_can_update_role(): void
    {
        $role = $this->roleService->create([
            'name' => 'Original Name',
            'is_active' => true,
        ]);

        $updated = $this->roleService->update($role, [
            'name' => 'Updated Name',
        ]);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals('updated-name', $updated->slug);
    }

    /**
     * Test: Service can delete role
     */
    public function test_service_can_delete_role(): void
    {
        $role = $this->roleService->create([
            'name' => 'To Delete',
            'is_active' => true,
        ]);

        $result = $this->roleService->delete($role);

        $this->assertTrue($result);
        $this->assertNull(Role::find($role->id));
    }

    /**
     * Test: Service can get all roles
     */
    public function test_service_can_get_all_roles(): void
    {
        $this->roleService->create(['name' => 'Role 1', 'is_active' => true]);
        $this->roleService->create(['name' => 'Role 2', 'is_active' => true]);

        $roles = $this->roleService->getAll();

        $this->assertEquals(2, $roles->count());
    }

    /**
     * Test: Service can get active roles
     */
    public function test_service_can_get_active_roles(): void
    {
        $this->roleService->create(['name' => 'Active', 'is_active' => true]);
        $this->roleService->create(['name' => 'Inactive', 'is_active' => false]);

        $roles = $this->roleService->getActive();

        $this->assertEquals(1, $roles->count());
    }

    /**
     * Test: Service can get role by ID
     */
    public function test_service_can_get_role_by_id(): void
    {
        $role = $this->roleService->create(['name' => 'Test', 'is_active' => true]);

        $retrieved = $this->roleService->getById($role->id);

        $this->assertEquals($role->id, $retrieved->id);
    }

    /**
     * Test: Service can get role by slug
     */
    public function test_service_can_get_role_by_slug(): void
    {
        $role = $this->roleService->create(['name' => 'Editor', 'is_active' => true]);

        $retrieved = $this->roleService->getBySlug('editor');

        $this->assertEquals($role->id, $retrieved->id);
    }

    /**
     * Test: Service can assign permission to role
     */
    public function test_service_can_assign_permission_to_role(): void
    {
        $role = $this->roleService->create(['name' => 'Editor', 'is_active' => true]);

        $this->roleService->assignPermission($role, $this->permission);

        $this->assertTrue($role->hasPermission('posts.view'));
    }

    /**
     * Test: Service can assign multiple permissions
     */
    public function test_service_can_assign_multiple_permissions(): void
    {
        $role = $this->roleService->create(['name' => 'Editor', 'is_active' => true]);

        $perm2 = Permission::create([
            'name' => 'Edit Posts',
            'slug' => 'posts.edit',
            'category' => 'posts',
            'is_active' => true,
        ]);

        $this->roleService->assignPermissions($role, [$this->permission->id, $perm2->id]);

        $this->assertEquals(2, $role->permissions()->count());
    }

    /**
     * Test: Service can remove permission from role
     */
    public function test_service_can_remove_permission_from_role(): void
    {
        $role = $this->roleService->create(['name' => 'Editor', 'is_active' => true]);
        $this->roleService->assignPermission($role, $this->permission);

        $this->roleService->removePermission($role, $this->permission);

        $this->assertFalse($role->hasPermission('posts.view'));
    }

    /**
     * Test: Service can sync permissions
     */
    public function test_service_can_sync_permissions(): void
    {
        $role = $this->roleService->create(['name' => 'Editor', 'is_active' => true]);
        $this->roleService->assignPermission($role, $this->permission);

        $perm2 = Permission::create([
            'name' => 'Edit Posts',
            'slug' => 'posts.edit',
            'category' => 'posts',
            'is_active' => true,
        ]);

        $this->roleService->syncPermissions($role, [$perm2->id]);

        $this->assertEquals(1, $role->permissions()->count());
        $this->assertFalse($role->hasPermission('posts.view'));
        $this->assertTrue($role->hasPermission('posts.edit'));
    }

    /**
     * Test: Service can check if role has permission
     */
    public function test_service_can_check_if_role_has_permission(): void
    {
        $role = $this->roleService->create(['name' => 'Editor', 'is_active' => true]);
        $this->roleService->assignPermission($role, $this->permission);

        $this->assertTrue($this->roleService->hasPermission($role, 'posts.view'));
        $this->assertFalse($this->roleService->hasPermission($role, 'posts.edit'));
    }

    /**
     * Test: Service can get permission count
     */
    public function test_service_can_get_permission_count(): void
    {
        $role = $this->roleService->create(['name' => 'Editor', 'is_active' => true]);
        $this->roleService->assignPermission($role, $this->permission);

        $count = $this->roleService->getPermissionCount($role);

        $this->assertEquals(1, $count);
    }

    /**
     * Test: Service can activate role
     */
    public function test_service_can_activate_role(): void
    {
        $role = $this->roleService->create(['name' => 'Editor', 'is_active' => false]);

        $activated = $this->roleService->activate($role);

        $this->assertTrue($activated->is_active);
    }

    /**
     * Test: Service can deactivate role
     */
    public function test_service_can_deactivate_role(): void
    {
        $role = $this->roleService->create(['name' => 'Editor', 'is_active' => true]);

        $deactivated = $this->roleService->deactivate($role);

        $this->assertFalse($deactivated->is_active);
    }
}
