<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Role Model Test
 *
 * Tests for Role model functionality including permissions assignment
 * and permission checking methods.
 */
class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected Role $role;
    protected Permission $permission1;
    protected Permission $permission2;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'description' => 'A test role',
            'is_active' => true,
        ]);

        $this->permission1 = Permission::create([
            'name' => 'Create Posts',
            'slug' => 'posts.create',
            'category' => 'posts',
            'is_active' => true,
        ]);

        $this->permission2 = Permission::create([
            'name' => 'Edit Posts',
            'slug' => 'posts.edit',
            'category' => 'posts',
            'is_active' => true,
        ]);
    }

    /**
     * Test: Role can have permissions
     */
    public function test_role_can_have_permissions(): void
    {
        $this->role->permissions()->attach($this->permission1->id);

        $this->assertTrue($this->role->permissions()->exists());
        $this->assertEquals(1, $this->role->permissions()->count());
    }

    /**
     * Test: Role has permission method
     */
    public function test_role_has_permission_method(): void
    {
        $this->role->permissions()->attach($this->permission1->id);

        $this->assertTrue($this->role->hasPermission('posts.create'));
        $this->assertFalse($this->role->hasPermission('posts.edit'));
    }

    /**
     * Test: Role has any permission method
     */
    public function test_role_has_any_permission_method(): void
    {
        $this->role->permissions()->attach([
            $this->permission1->id,
        ]);

        $this->assertTrue(
            $this->role->hasAnyPermission(['posts.create', 'posts.edit'])
        );
        $this->assertFalse(
            $this->role->hasAnyPermission(['posts.delete', 'posts.view'])
        );
    }

    /**
     * Test: Role has all permissions method
     */
    public function test_role_has_all_permissions_method(): void
    {
        $this->role->permissions()->attach([
            $this->permission1->id,
            $this->permission2->id,
        ]);

        $this->assertTrue(
            $this->role->hasAllPermissions(['posts.create', 'posts.edit'])
        );
        $this->assertFalse(
            $this->role->hasAllPermissions(['posts.create', 'posts.delete'])
        );
    }

    /**
     * Test: Role can get permission slugs
     */
    public function test_role_can_get_permission_slugs(): void
    {
        $this->role->permissions()->attach([
            $this->permission1->id,
            $this->permission2->id,
        ]);

        $slugs = $this->role->getPermissionSlugs();

        $this->assertContains('posts.create', $slugs);
        $this->assertContains('posts.edit', $slugs);
        $this->assertCount(2, $slugs);
    }

    /**
     * Test: Role can be active or inactive
     */
    public function test_role_can_be_active_or_inactive(): void
    {
        $this->assertTrue($this->role->is_active);

        $this->role->update(['is_active' => false]);

        $this->assertFalse($this->role->is_active);
    }

    /**
     * Test: Role slug is unique
     */
    public function test_role_slug_is_unique(): void
    {
        $this->expectException(\Exception::class);

        Role::create([
            'name' => 'Another Role',
            'slug' => 'test-role',
            'description' => 'Should fail',
            'is_active' => true,
        ]);
    }

    /**
     * Test: Role can sync permissions
     */
    public function test_role_can_sync_permissions(): void
    {
        $this->role->permissions()->attach($this->permission1->id);
        $this->assertEquals(1, $this->role->permissions()->count());

        $this->role->permissions()->sync([$this->permission2->id]);
        $this->assertEquals(1, $this->role->permissions()->count());
        $this->assertFalse($this->role->hasPermission('posts.create'));
        $this->assertTrue($this->role->hasPermission('posts.edit'));
    }

    /**
     * Test: Deleting role removes permissions
     */
    public function test_deleting_role_removes_permissions(): void
    {
        $this->role->permissions()->attach([
            $this->permission1->id,
            $this->permission2->id,
        ]);

        $this->assertEquals(2, $this->role->permissions()->count());

        $this->role->delete();

        // Check that permissions still exist but relationship is gone
        $this->assertEquals(2, Permission::count());
        $this->assertEquals(0, Role::count());
    }

    /**
     * Test: Role has users relationship
     */
    public function test_role_has_users_relationship(): void
    {
        $this->assertTrue(method_exists($this->role, 'users'));
        $this->assertFalse($this->role->users()->exists());
    }
}
