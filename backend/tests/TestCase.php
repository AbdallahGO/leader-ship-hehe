<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * TestCase Base Class
 * 
 * Base test class that all tests should extend.
 * Provides common test setup and utilities.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment
     */
    public function setUp(): void
    {
        parent::setUp();
        
        // Run migrations for each test
        $this->artisan('migrate:refresh', ['--database' => 'sqlite']);
    }

    /**
     * Tear down the test environment
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Helper method to create an authenticated user
     * 
     * @param array $attributes
     * @return \App\Models\User
     */
    protected function createAuthenticatedUser(array $attributes = [])
    {
        $user = \App\Models\User::factory()->create($attributes);
        $this->actingAs($user);
        return $user;
    }

    /**
     * Helper method to create an admin user
     * 
     * @param array $attributes
     * @return \App\Models\User
     */
    protected function createAdminUser(array $attributes = [])
    {
        $user = \App\Models\User::factory()->create($attributes);
        $adminRole = \App\Models\Role::where('name', 'Administrator')->first();
        if ($adminRole) {
            $user->roles()->attach($adminRole);
        }
        $this->actingAs($user);
        return $user;
    }

    /**
     * Helper method to create multiple users
     * 
     * @param int $count
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function createUsers(int $count = 5, array $attributes = [])
    {
        return \App\Models\User::factory()->count($count)->create($attributes);
    }

    /**
     * Helper method to assert JSON response has status
     * 
     * @param \Illuminate\Testing\TestResponse $response
     * @param int $status
     * @return void
     */
    protected function assertJsonStatus($response, $status)
    {
        $this->assertEquals($status, $response->getStatusCode(), 
            "Expected status {$status}, got {$response->getStatusCode()}. Response: {$response->getContent()}");
    }

    /**
     * Helper method to assert JSON response has error
     * 
     * @param \Illuminate\Testing\TestResponse $response
     * @param string $message
     * @return void
     */
    protected function assertJsonHasError($response, $message = null)
    {
        $response->assertJsonPath('success', false);
        if ($message) {
            $response->assertJsonPath('message', $message);
        }
    }

    /**
     * Helper method to assert JSON response has success
     * 
     * @param \Illuminate\Testing\TestResponse $response
     * @param string $message
     * @return void
     */
    protected function assertJsonSuccess($response, $message = null)
    {
        $response->assertJsonPath('success', true);
        if ($message) {
            $response->assertJsonPath('message', $message);
        }
    }
}
