<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FileValidationService;
use App\Services\ImageOptimizationService;
use App\Services\UploadService;
use Illuminate\Http\UploadedFile;

/**
 * File Upload Services Tests
 */
class FileUploadTest extends TestCase
{
    /**
     * Test file validation - valid image
     */
    public function test_validates_valid_image()
    {
        $validator = app(FileValidationService::class);
        $file = UploadedFile::fake()->image('avatar.jpg', width: 500, height: 500);

        $this->assertTrue($validator->validate($file, 'avatar'));
        $this->assertEmpty($validator->getErrors());
    }

    /**
     * Test file validation - file too large
     */
    public function test_rejects_oversized_file()
    {
        $validator = app(FileValidationService::class);
        $file = UploadedFile::fake()->image('avatar.jpg')->size(10240); // 10MB

        $this->assertFalse($validator->validate($file, 'avatar'));
        $this->assertNotEmpty($validator->getErrors());
        $this->assertStringContainsString('exceeds maximum', $validator->getFirstError());
    }

    /**
     * Test file validation - invalid extension
     */
    public function test_rejects_invalid_extension()
    {
        $validator = app(FileValidationService::class);
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->assertFalse($validator->validate($file, 'avatar'));
        $this->assertStringContainsString('extension', $validator->getFirstError());
    }

    /**
     * Test file validation - image dimensions too small
     */
    public function test_rejects_image_too_small()
    {
        $validator = app(FileValidationService::class);
        $file = UploadedFile::fake()->image('avatar.jpg', width: 50, height: 50);

        $this->assertFalse($validator->validate($file, 'avatar'));
        $this->assertStringContainsString('dimension', $validator->getFirstError());
    }

    /**
     * Test file validation - image dimensions too large
     */
    public function test_rejects_image_too_large()
    {
        $validator = app(FileValidationService::class);
        $file = UploadedFile::fake()->image('avatar.jpg', width: 5000, height: 5000);

        $this->assertFalse($validator->validate($file, 'avatar'));
        $this->assertStringContainsString('dimension', $validator->getFirstError());
    }

    /**
     * Test image optimization - size reduction
     */
    public function test_optimizes_image_reduces_size()
    {
        $optimizer = app(ImageOptimizationService::class);
        $file = UploadedFile::fake()->image('avatar.jpg', width: 800, height: 800);

        $result = $optimizer->process($file, ['generate_variants' => false]);

        $this->assertArrayHasKey('original', $result);
        $this->assertArrayHasKey('size_reduction', $result);
        $this->assertGreaterThan(0, $result['size_reduction']);
    }

    /**
     * Test image optimization - generates variants
     */
    public function test_generates_image_variants()
    {
        $optimizer = app(ImageOptimizationService::class);
        $file = UploadedFile::fake()->image('avatar.jpg', width: 800, height: 800);

        $result = $optimizer->process($file, ['generate_variants' => true]);

        $this->assertArrayHasKey('variants', $result);
        $this->assertArrayHasKey('thumb', $result['variants']);
        $this->assertArrayHasKey('medium', $result['variants']);
        $this->assertArrayHasKey('large', $result['variants']);
    }

    /**
     * Test image optimization - variant dimensions
     */
    public function test_variant_dimensions_correct()
    {
        $optimizer = app(ImageOptimizationService::class);
        $file = UploadedFile::fake()->image('avatar.jpg', width: 800, height: 800);

        $result = $optimizer->process($file, ['generate_variants' => true]);

        $this->assertEquals(150, $result['variants']['thumb']['width']);
        $this->assertEquals(300, $result['variants']['medium']['width']);
        $this->assertEquals(800, $result['variants']['large']['width']);
    }

    /**
     * Test image optimization - maintains aspect ratio
     */
    public function test_maintains_aspect_ratio()
    {
        $optimizer = app(ImageOptimizationService::class);
        $file = UploadedFile::fake()->image('avatar.jpg', width: 1000, height: 500);

        $result = $optimizer->process($file, ['generate_variants' => true]);

        // Check aspect ratio maintained in original
        $originalRatio = $result['original']['width'] / $result['original']['height'];
        $this->assertEquals(2.0, $originalRatio, '', 0.1);

        // Check aspect ratio maintained in variants
        $thumbRatio = $result['variants']['thumb']['width'] / $result['variants']['thumb']['height'];
        $this->assertEquals(2.0, $thumbRatio, '', 0.1);
    }

    /**
     * Test file type detection
     */
    public function test_detects_file_types()
    {
        $validator = app(FileValidationService::class);

        $imageFile = UploadedFile::fake()->image('avatar.jpg');
        $this->assertTrue($validator->isImage($imageFile));
        $this->assertFalse($validator->isVideo($imageFile));
        $this->assertFalse($validator->isDocument($imageFile));
    }

    /**
     * Test readable file size formatting
     */
    public function test_formats_file_size()
    {
        $optimizer = app(ImageOptimizationService::class);

        $this->assertEquals('1.5 KB', $optimizer->getReadableFileSize(1536));
        $this->assertEquals('2.3 MB', $optimizer->getReadableFileSize(2411724));
        $this->assertEquals('1.5 GB', $optimizer->getReadableFileSize(1610612736));
    }

    /**
     * Test multiple file validations
     */
    public function test_validates_multiple_files()
    {
        $validator = app(FileValidationService::class);

        $files = [
            UploadedFile::fake()->image('avatar1.jpg', width: 500, height: 500),
            UploadedFile::fake()->image('avatar2.png', width: 600, height: 600),
            UploadedFile::fake()->image('avatar3.gif', width: 700, height: 700),
        ];

        foreach ($files as $file) {
            $this->assertTrue($validator->validate($file, 'avatar'));
        }
    }

    /**
     * Test error message for each validation failure
     */
    public function test_provides_clear_error_messages()
    {
        $validator = app(FileValidationService::class);

        // Test size error message
        $file = UploadedFile::fake()->image('avatar.jpg')->size(10240);
        $validator->validate($file, 'avatar');
        $this->assertStringContainsString('KB', $validator->getFirstError());
        $this->assertStringContainsString('exceeds maximum', $validator->getFirstError());

        // Test dimension error message
        $file = UploadedFile::fake()->image('avatar.jpg', width: 50, height: 50);
        $validator->validate($file, 'avatar');
        $this->assertStringContainsString('width', $validator->getFirstError());
        $this->assertStringContainsString('50px', $validator->getFirstError());
    }
}
