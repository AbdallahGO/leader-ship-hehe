<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

/**
 * Image Optimization Service
 *
 * Handles image processing, resizing, and optimization
 * Generates thumbnail variants and optimizes file size
 */
class ImageOptimizationService
{
    /**
     * Image variants to generate
     */
    private array $variants = [
        'thumb' => ['width' => 150, 'height' => 150],
        'medium' => ['width' => 300, 'height' => 300],
        'large' => ['width' => 800, 'height' => 800],
    ];

    /**
     * Quality settings for different formats
     */
    private array $quality = [
        'jpg' => 85,
        'jpeg' => 85,
        'png' => 9,
        'webp' => 85,
    ];

    /**
     * Process and optimize image
     *
     * @param UploadedFile $file
     * @param array $options Processing options
     * @return array Processing results
     */
    public function process(UploadedFile $file, array $options = []): array
    {
        $results = [
            'original' => null,
            'variants' => [],
            'size_reduction' => 0,
            'format' => $this->getOptimalFormat($file),
        ];

        try {
            // Load image
            $image = Image::make($file->getRealPath());

            // Get original dimensions
            $originalWidth = $image->width();
            $originalHeight = $image->height();

            // Optimize original
            $results['original'] = $this->optimizeImage(
                $image,
                $file,
                $options['filename'] ?? $file->getClientOriginalName()
            );

            // Generate variants
            if ($options['generate_variants'] ?? true) {
                foreach ($this->variants as $variantName => $variantSize) {
                    $results['variants'][$variantName] = $this->generateVariant(
                        $image,
                        $variantName,
                        $variantSize,
                        $file->getClientOriginalExtension()
                    );
                }
            }

            // Calculate size reduction
            $originalSize = $file->getSize();
            $optimizedSize = filesize($results['original']['path']) ?? $originalSize;
            $results['size_reduction'] = round(
                (($originalSize - $optimizedSize) / $originalSize) * 100,
                2
            );

            return $results;
        } catch (\Exception $e) {
            report($e);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Optimize single image
     */
    private function optimizeImage($image, UploadedFile $file, string $filename): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $quality = $this->quality[$extension] ?? 85;

        // Resize if too large
        if ($image->width() > 4000 || $image->height() > 4000) {
            $image->resize(4000, 4000, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Save optimized image
        $filename = $this->generateFilename($filename);
        $path = storage_path('uploads/avatars/' . $filename);

        // Ensure directory exists
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        // Save with optimization
        if ($extension === 'png') {
            $image->save($path, $quality);
        } else {
            $image->save($path, $quality);
        }

        return [
            'filename' => $filename,
            'path' => $path,
            'url' => "/storage/uploads/avatars/{$filename}",
            'size' => filesize($path),
            'width' => $image->width(),
            'height' => $image->height(),
            'mime_type' => mime_content_type($path),
        ];
    }

    /**
     * Generate image variant
     */
    private function generateVariant($image, string $variantName, array $size, string $extension): array
    {
        $variant = $image->resize(
            $size['width'],
            $size['height'],
            function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            }
        );

        $quality = $this->quality[$extension] ?? 85;
        $filename = $this->generateVariantFilename($variantName, $extension);
        $path = storage_path("uploads/avatars/variants/{$filename}");

        // Ensure directory exists
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $variant->save($path, $quality);

        return [
            'filename' => $filename,
            'path' => $path,
            'url' => "/storage/uploads/avatars/variants/{$filename}",
            'size' => filesize($path),
            'width' => $variant->width(),
            'height' => $variant->height(),
            'variant' => $variantName,
        ];
    }

    /**
     * Get optimal image format
     */
    private function getOptimalFormat(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        // Prefer WebP for better compression
        $supportedFormats = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        return in_array($extension, $supportedFormats) ? $extension : 'jpg';
    }

    /**
     * Generate unique filename
     */
    private function generateFilename(string $originalFilename): string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $timestamp = now()->timestamp;
        $random = str_pad(random_int(1, 999), 3, '0', STR_PAD_LEFT);

        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Generate variant filename
     */
    private function generateVariantFilename(string $variantName, string $extension): string
    {
        $timestamp = now()->timestamp;
        $random = str_pad(random_int(1, 999), 3, '0', STR_PAD_LEFT);

        return "{$timestamp}_{$random}_{$variantName}.{$extension}";
    }

    /**
     * Get image dimensions
     */
    public function getDimensions(string $filePath): ?array
    {
        $imageInfo = getimagesize($filePath);

        if ($imageInfo === false) {
            return null;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime' => $imageInfo['mime'],
        ];
    }

    /**
     * Resize image to specific dimensions
     */
    public function resize(string $filePath, int $width, int $height, string $outputPath): bool
    {
        try {
            $image = Image::make($filePath);
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Ensure output directory exists
            if (!is_dir(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0755, true);
            }

            $image->save($outputPath);
            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Convert image format
     */
    public function convertFormat(string $filePath, string $format, string $outputPath): bool
    {
        try {
            $image = Image::make($filePath);
            $quality = $this->quality[$format] ?? 85;

            // Ensure output directory exists
            if (!is_dir(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0755, true);
            }

            $image->save($outputPath, $quality);
            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Get file size in human readable format
     */
    public function getReadableFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
