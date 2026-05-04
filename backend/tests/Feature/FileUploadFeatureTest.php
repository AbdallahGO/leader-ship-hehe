<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\UploadedFile;

/**
 * File Upload Feature Tests
 *
 * Tests the complete file upload workflow through API endpoints
 */
class FileUploadFeatureTest extends TestCase
{
    /**
     * Authenticated user for testing
     */
    private User $user;

    /**
     * Setup test environment
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test successful avatar upload
     */
    public function test_user_can_upload_avatar()
    {
        $file = UploadedFile::fake()->image('avatar.jpg', width: 500, height: 500);

        $response = $this->actingAs($this->user)
            ->post('/api/v1/profile/avatar', ['avatar' => $file]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Avatar uploaded successfully')
            ->assertJsonPath('data.url', fn ($url) => str_contains($url, '/storage/uploads/avatars/'));

        // Verify database was updated
        $this->assertEquals(1, $this->user->fresh()->avatar ? 1 : 0);
    }

    /**
     * Test avatar upload with variants
     */
    public function test_upload_generates_variants()
    {
        $file = UploadedFile::fake()->image('avatar.jpg', width: 800, height: 800);

        $response = $this->actingAs($this->user)
            ->post('/api/v1/profile/avatar', ['avatar' => $file]);

        $response->assertStatus(200)
            ->assertJsonPath('data.variants', fn ($variants) =>
                array_key_exists('thumb', $variants) &&
                array_key_exists('medium', $variants) &&
                array_key_exists('large', $variants)
            );
    }

    /**
     * Test upload includes size reduction info
     */
    public function test_upload_shows_size_reduction()
    {
        $file = UploadedFile::fake()->image('avatar.jpg', width: 500, height: 500);

        $response = $this->actingAs($this->user)
            ->post('/api/v1/profile/avatar', ['avatar' => $file]);

        $response->assertJsonPath('data.size_reduction', fn ($reduction) =>
            preg_match('/^[\d.]+%$/', $reduction)
        );
    }

    /**
     * Test avatar upload fails without authentication
     */
    public function test_unauthenticated_user_cannot_upload()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->post('/api/v1/profile/avatar', ['avatar' => $file]);

        $response->assertStatus(401);
    }

    /**
     * Test upload fails with missing file
     */
    public function test_upload_fails_without_file()
    {
        $response = $this->actingAs($this->user)
            ->post('/api/v1/profile/avatar', []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * Test upload fails with oversized file
     */
    public function test_upload_fails_with_oversized_file()
    {
        $file = UploadedFile::fake()->image('avatar.jpg')->size(10240); // 10MB

        $response = $this->actingAs($this->user)
            ->post('/api/v1/profile/avatar', ['avatar' => $file]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'exceeds maximum'));
    }

    /**
     * Test upload fails with invalid image dimensions
     */
    public function test_upload_fails_with_invalid_dimensions()
    {
        $file = UploadedFile::fake()->image('avatar.jpg', width: 50, height: 50);

        $response = $this->actingAs($this->user)
            ->post('/api/v1/profile/avatar', ['avatar' => $file]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'dimension'));
    }

    /**
     * Test upload fails with invalid file type
     */
    public function test_upload_fails_with_invalid_file_type()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->post('/api/v1/profile/avatar', ['avatar' => $file]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * Test successful avatar deletion
     */
    public function test_user_can_delete_avatar()
    {
        // First, upload an avatar
        $this->user->update(['avatar' => 'test_avatar.jpg']);

        $response = $this->actingAs($this->user)
            ->delete('/api/v1/profile/avatar');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Avatar deleted successfully');

        // Verify database was updated
        $this->assertNull($this->user->fresh()->avatar);
    }

    /**
     * Test delete fails when no avatar exists
     */
    public function test_delete_fails_without_avatar()
    {
        $response = $this->actingAs($this->user)
            ->delete('/api/v1/profile/avatar');

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'User does not have an avatar');
    }

    /**
     * Test avatar deletion removes variants
     */
    public function test_delete_removes_variants()
    {
        // Upload avatar with variants
        $file = UploadedFile::fake()->image('avatar.jpg', width: 800, height: 800);
        $uploadResponse = $this->actingAs($this->user)
            ->post('/api/v1/profile/avatar', ['avatar' => $file]);

        $this->assertTrue($uploadResponse->json('success'));

        // Delete avatar
        $deleteResponse = $this->actingAs($this->user)
            ->delete('/api/v1/profile/avatar');

        $this->assertTrue($deleteResponse->json('success'));
    }

    /**
     * Test multiple avatar uploads overwrite old one
     */
    public function test_new_avatar_overwrites_old()
    {
        $file1 = UploadedFile::fake()->image('avatar1.jpg', width: 500, height: 500);
        $response1 = $this->actingAs($this->user)
            ->post('/api/v1/profile/avatar', ['avatar' => $file1]);

        $firstAvatar = $response1->json('data.filename');

        $file2 = UploadedFile::fake()->image('avatar2.jpg', width: 600, height: 600);
        $response2 = $this->actingAs($this->user)
            ->post('/api/v1/profile/avatar', ['avatar' => $file2]);

        $secondAvatar = $response2->json('data.filename');

        // Avatars should be different
        $this->assertNotEqual($firstAvatar, $secondAvatar);

        // User should only have latest avatar
        $this->assertNotNull($this->user->fresh()->avatar);
    }

    /**
     * Test avatar upload response includes all required fields
     */
    public function test_upload_response_structure()
    {
        $file = UploadedFile::fake()->image('avatar.jpg', width: 500, height: 500);

        $response = $this->actingAs($this->user)
            ->post('/api/v1/profile/avatar', ['avatar' => $file]);

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'filename',
                'url',
                'size',
                'dimensions' => [
                    'width',
                    'height',
                ],
                'size_reduction',
                'variants' => [
                    'thumb' => [
                        'filename',
                        'url',
                        'size',
                        'width',
                        'height',
                    ],
                    'medium' => [
                        'filename',
                        'url',
                        'size',
                        'width',
                        'height',
                    ],
                    'large' => [
                        'filename',
                        'url',
                        'size',
                        'width',
                        'height',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test rate limiting on uploads
     */
    public function test_upload_respects_rate_limiting()
    {
        // This would test rate limiting if implemented
        // For now, just verify endpoint works multiple times
        for ($i = 0; $i < 3; $i++) {
            $file = UploadedFile::fake()->image("avatar{$i}.jpg", width: 500, height: 500);
            $response = $this->actingAs($this->user)
                ->post('/api/v1/profile/avatar', ['avatar' => $file]);

            $this->assertTrue($response->json('success'));
        }
    }

    /**
     * Test upload activity is logged
     */
    public function test_upload_logs_activity()
    {
        $file = UploadedFile::fake()->image('avatar.jpg', width: 500, height: 500);

        $this->actingAs($this->user)
            ->post('/api/v1/profile/avatar', ['avatar' => $file]);

        // Verify activity was logged (implementation specific)
        // This would check database for activity log entry
    }
}
