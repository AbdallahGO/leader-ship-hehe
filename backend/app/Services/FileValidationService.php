<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

/**
 * File Validation Service
 *
 * Comprehensive file validation for uploads
 * Validates MIME type, size, dimensions, and content
 */
class FileValidationService
{
    /**
     * Configuration for file validation
     */
    private array $config;

    /**
     * Validation errors
     */
    private array $errors = [];

    /**
     * Create a new service instance
     */
    public function __construct()
    {
        $this->config = config('uploads', []);
    }

    /**
     * Validate an uploaded file
     *
     * @param UploadedFile $file
     * @param string $type Type of upload (avatar, document, etc.)
     * @return bool
     */
    public function validate(UploadedFile $file, string $type = 'avatar'): bool
    {
        $this->errors = [];

        // Validate file exists
        if (!$file || !$file->isValid()) {
            $this->errors[] = 'File upload failed or file is invalid.';
            return false;
        }

        // Validate file size
        if (!$this->validateSize($file)) {
            return false;
        }

        // Validate file extension
        if (!$this->validateExtension($file, $type)) {
            return false;
        }

        // Validate MIME type
        if (!$this->validateMimeType($file, $type)) {
            return false;
        }

        // Type-specific validation
        return $this->validateByType($file, $type);
    }

    /**
     * Validate file size
     */
    private function validateSize(UploadedFile $file): bool
    {
        $maxSize = $this->config['max_size'] ?? 5120; // 5MB default
        $fileSizeKB = $file->getSize() / 1024;

        if ($fileSizeKB > $maxSize) {
            $this->errors[] = sprintf(
                'File size (%dKB) exceeds maximum allowed size (%dKB).',
                $fileSizeKB,
                $maxSize
            );
            return false;
        }

        return true;
    }

    /**
     * Validate file extension
     */
    private function validateExtension(UploadedFile $file, string $type): bool
    {
        $allowedExtensions = $this->getAllowedExtensions($type);
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedExtensions)) {
            $this->errors[] = sprintf(
                'File extension .%s is not allowed. Allowed: %s',
                $extension,
                implode(', ', $allowedExtensions)
            );
            return false;
        }

        return true;
    }

    /**
     * Validate MIME type
     */
    private function validateMimeType(UploadedFile $file, string $type): bool
    {
        $allowedMimes = $this->getAllowedMimes($type);
        $mimeType = $file->getMimeType();

        if (!in_array($mimeType, $allowedMimes)) {
            $this->errors[] = sprintf(
                'File MIME type %s is not allowed. Allowed types: %s',
                $mimeType,
                implode(', ', $allowedMimes)
            );
            return false;
        }

        return true;
    }

    /**
     * Type-specific validation
     */
    private function validateByType(UploadedFile $file, string $type): bool
    {
        return match ($type) {
            'avatar' => $this->validateImageDimensions($file),
            'document' => $this->validateDocument($file),
            'video' => $this->validateVideo($file),
            default => true,
        };
    }

    /**
     * Validate image dimensions
     */
    private function validateImageDimensions(UploadedFile $file): bool
    {
        $imageInfo = getimagesize($file->getRealPath());

        if ($imageInfo === false) {
            $this->errors[] = 'File is not a valid image.';
            return false;
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];

        $minWidth = $this->config['image_min_width'] ?? 100;
        $maxWidth = $this->config['image_max_width'] ?? 4000;
        $minHeight = $this->config['image_min_height'] ?? 100;
        $maxHeight = $this->config['image_max_height'] ?? 4000;

        if ($width < $minWidth || $width > $maxWidth) {
            $this->errors[] = sprintf(
                'Image width (%dpx) must be between %dpx and %dpx.',
                $width,
                $minWidth,
                $maxWidth
            );
            return false;
        }

        if ($height < $minHeight || $height > $maxHeight) {
            $this->errors[] = sprintf(
                'Image height (%dpx) must be between %dpx and %dpx.',
                $height,
                $minHeight,
                $maxHeight
            );
            return false;
        }

        return true;
    }

    /**
     * Validate document file
     */
    private function validateDocument(UploadedFile $file): bool
    {
        // Check for malicious content
        $content = file_get_contents($file->getRealPath(), false, null, 0, 512);

        if (strpos($content, '<?php') !== false || strpos($content, '<?') !== false) {
            $this->errors[] = 'Document contains PHP code, which is not allowed.';
            return false;
        }

        return true;
    }

    /**
     * Validate video file
     */
    private function validateVideo(UploadedFile $file): bool
    {
        // Video-specific validation can be added here
        return true;
    }

    /**
     * Get allowed extensions for file type
     */
    private function getAllowedExtensions(string $type): array
    {
        return match ($type) {
            'avatar' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'document' => ['pdf', 'doc', 'docx', 'xlsx', 'txt'],
            'video' => ['mp4', 'avi', 'mov', 'webm'],
            default => [],
        };
    }

    /**
     * Get allowed MIME types for file type
     */
    private function getAllowedMimes(string $type): array
    {
        return match ($type) {
            'avatar' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'video' => ['video/mp4', 'video/x-msvideo', 'video/quicktime', 'video/webm'],
            default => [],
        };
    }

    /**
     * Get validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error message
     */
    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Check if file is an image
     */
    public function isImage(UploadedFile $file): bool
    {
        return str_starts_with($file->getMimeType(), 'image/');
    }

    /**
     * Check if file is a video
     */
    public function isVideo(UploadedFile $file): bool
    {
        return str_starts_with($file->getMimeType(), 'video/');
    }

    /**
     * Check if file is a document
     */
    public function isDocument(UploadedFile $file): bool
    {
        $documentMimes = ['application/pdf', 'application/msword'];
        return in_array($file->getMimeType(), $documentMimes);
    }
}
