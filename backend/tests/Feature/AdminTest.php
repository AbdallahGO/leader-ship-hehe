<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin Features Test
 *
 * Tests for all admin functionality including user management,
 * role assignment, banning, suspension, and activity logging.
 */
class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $testUser;
    protected Role $adminRole;
    protected Role $userRole;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->adminRole = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Full system access',
            'is_active' => true,
        ]);

        $this->userRole = Role::create([
            'name' => 'User',
            'slug' => 'user',
            'description' => 'Regular user',
            'is_active' => true,
        ]);

        // Create admin user
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->adminUser->roles()->attach($this->adminRole->id);

        // Create test user
        $this->testUser = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
        $this->testUser->roles()->attach($this->userRole->id);
    }

    /**
     * Test: List all users
     */
    public function test_admin_can_list_all_users(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ]);
    }

    /**
     * Test: Get user details
     */
    public function test_admin_can_get_user_details(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/v1/admin/users/{$this->testUser->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'is_active',
                    'is_banned',
                    'is_suspended',
                    'roles',
                ],
            ]);
    }

    /**
     * Test: Search users
     */
    public function test_admin_can_search_users(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/users/search?q=Test');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'pagination',
            ]);
    }

    /**
     * Test: Update user information
     */
    public function test_admin_can_update_user(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->putJson("/api/v1/admin/users/{$this->testUser->id}", [
                'name' => 'Updated User Name',
                'email' => 'updated@example.com',
            ]);

        $response->assertStatus(200);

        $this->testUser->refresh();
        $this->assertEquals('Updated User Name', $this->testUser->name);
    }

    /**
     * Test: Assign role to user
     */
    public function test_admin_can_assign_role_to_user(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/admin/users/{$this->testUser->id}/roles", [
                'role_id' => $this->adminRole->id,
            ]);

        $response->assertStatus(200);

        $this->testUser->refresh();
        $this->assertTrue($this->testUser->hasRole('admin'));
    }

    /**
     * Test: Ban user
     */
    public function test_admin_can_ban_user(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/admin/users/{$this->testUser->id}/ban", [
                'reason' => 'Violation of terms',
            ]);

        $response->assertStatus(200);

        $this->testUser->refresh();
        $this->assertTrue($this->testUser->is_banned);
        $this->assertNotNull($this->testUser->banned_at);
    }

    /**
     * Test: Unban user
     */
    public function test_admin_can_unban_user(): void
    {
        $this->testUser->ban('Test reason');

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/admin/users/{$this->testUser->id}/unban");

        $response->assertStatus(200);

        $this->testUser->refresh();
        $this->assertFalse($this->testUser->is_banned);
    }

    /**
     * Test: Suspend user
     */
    public function test_admin_can_suspend_user(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/admin/users/{$this->testUser->id}/suspend", [
                'reason' => 'Account under review',
            ]);

        $response->assertStatus(200);

        $this->testUser->refresh();
        $this->assertTrue($this->testUser->is_suspended);
    }

    /**
     * Test: Unsuspend user
     */
    public function test_admin_can_unsuspend_user(): void
    {
        $this->testUser->suspend('Test reason');

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/admin/users/{$this->testUser->id}/unsuspend");

        $response->assertStatus(200);

        $this->testUser->refresh();
        $this->assertFalse($this->testUser->is_suspended);
    }

    /**
     * Test: Delete user
     */
    public function test_admin_can_delete_user(): void
    {
        $userId = $this->testUser->id;

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/v1/admin/users/{$userId}");

        $response->assertStatus(200);

        $this->assertNull(User::find($userId));
    }

    /**
     * Test: Get user activity logs
     */
    public function test_admin_can_get_user_activities(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/v1/admin/users/{$this->testUser->id}/activities");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    /**
     * Test: Get statistics
     */
    public function test_admin_can_get_statistics(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total_users',
                    'active_users',
                    'banned_users',
                    'suspended_users',
                ],
            ]);
    }

    /**
     * Test: List all roles
     */
    public function test_admin_can_list_roles(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/admin/roles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    /**
     * Test: Non-admin cannot access admin endpoints
     */
    public function test_non_admin_cannot_access_admin_endpoints(): void
    {
        $response = $this->actingAs($this->testUser)
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(403);
    }

    /**
     * Test: Unauthenticated user cannot access admin endpoints
     */
    public function test_unauthenticated_user_cannot_access_admin_endpoints(): void
    {
        $response = $this->getJson('/api/v1/admin/users');

        $response->assertStatus(401);
    }

    /**
     * Test: Admin cannot ban themselves
     */
    public function test_admin_cannot_ban_themselves(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/admin/users/{$this->adminUser->id}/ban");

        $response->assertStatus(400);
    }

    /**
     * Test: Admin cannot delete themselves
     */
    public function test_admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/v1/admin/users/{$this->adminUser->id}");

        $response->assertStatus(400);
    }
}
