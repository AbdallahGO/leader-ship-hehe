<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

/**
 * Upload Service
 *
 * Manages file uploads with validation, optimization, and storage
 * Coordinates between FileValidationService and ImageOptimizationService
 */
class UploadService
{
    /**
     * Services
     */
    private FileValidationService $validationService;
    private ImageOptimizationService $optimizationService;
    private ActivityLoggingService $activityLogger;

    /**
     * Create a new service instance
     */
    public function __construct(
        FileValidationService $validationService,
        ImageOptimizationService $optimizationService,
        ActivityLoggingService $activityLogger
    ) {
        $this->validationService = $validationService;
        $this->optimizationService = $optimizationService;
        $this->activityLogger = $activityLogger;
    }

    /**
     * Upload user avatar
     *
     * @param UploadedFile $file
     * @param User $user
     * @param array $options
     * @return array Upload result
     */
    public function uploadAvatar(UploadedFile $file, User $user, array $options = []): array
    {
        // Validate file
        if (!$this->validationService->validate($file, 'avatar')) {
            return [
                'success' => false,
                'message' => $this->validationService->getFirstError(),
                'errors' => $this->validationService->getErrors(),
            ];
        }

        try {
            // Process and optimize image
            $processResult = $this->optimizationService->process($file, [
                'generate_variants' => true,
                'filename' => "avatar_{$user->id}_" . time(),
            ]);

            if (isset($processResult['error'])) {
                return [
                    'success' => false,
                    'message' => 'Failed to process image: ' . $processResult['error'],
                ];
            }

            // Delete old avatar if exists
            if ($user->avatar) {
                $this->deleteAvatar($user);
            }

            // Update user avatar
            $user->update([
                'avatar' => $processResult['original']['filename'],
            ]);

            // Log activity
            $this->activityLogger->logAvatarUpload($processResult['original']['filename']);

            return [
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'data' => [
                    'filename' => $processResult['original']['filename'],
                    'url' => $processResult['original']['url'],
                    'size' => $processResult['original']['size'],
                    'dimensions' => [
                        'width' => $processResult['original']['width'],
                        'height' => $processResult['original']['height'],
                    ],
                    'size_reduction' => $processResult['size_reduction'] . '%',
                    'variants' => $processResult['variants'],
                ],
            ];
        } catch (\Exception $e) {
            report($e);
            return [
                'success' => false,
                'message' => 'Failed to upload avatar: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Upload document file
     *
     * @param UploadedFile $file
     * @param User $user
     * @param array $options
     * @return array Upload result
     */
    public function uploadDocument(UploadedFile $file, User $user, array $options = []): array
    {
        // Validate file
        if (!$this->validationService->validate($file, 'document')) {
            return [
                'success' => false,
                'message' => $this->validationService->getFirstError(),
                'errors' => $this->validationService->getErrors(),
            ];
        }

        try {
            $filename = "doc_{$user->id}_" . time() . '.' . $file->getClientOriginalExtension();
            $path = "documents/{$user->id}";

            // Store file
            $filePath = Storage::disk('uploads')->putFileAs(
                $path,
                $file,
                $filename
            );

            // Log activity
            $this->activityLogger->log('file:upload', json_encode([
                'type' => 'document',
                'filename' => $filename,
                'size' => $file->getSize(),
            ]));

            return [
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $filePath,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ],
            ];
        } catch (\Exception $e) {
            report($e);
            return [
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete user avatar
     */
    public function deleteAvatar(User $user): bool
    {
        if (!$user->avatar) {
            return true;
        }

        try {
            // Delete original
            $this->deleteFileByName($user->avatar, 'avatars');

            // Delete variants
            $this->deleteFileByName(str_replace('.', '_thumb.', $user->avatar), 'avatars/variants');
            $this->deleteFileByName(str_replace('.', '_medium.', $user->avatar), 'avatars/variants');
            $this->deleteFileByName(str_replace('.', '_large.', $user->avatar), 'avatars/variants');

            // Update user
            $user->update(['avatar' => null]);

            // Log activity
            $this->activityLogger->logAvatarDeletion();

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Delete file by name
     */
    private function deleteFileByName(string $filename, string $path): bool
    {
        try {
            $fullPath = "{$path}/{$filename}";
            if (Storage::disk('uploads')->exists($fullPath)) {
                Storage::disk('uploads')->delete($fullPath);
            }
            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Get user's upload history
     */
    public function getUploadHistory(User $user, int $limit = 50): array
    {
        return $this->activityLogger->getUserActivity($user->id, $limit)
            ->where('action', 'like', 'file:%')
            ->orWhere('action', 'like', 'profile:avatar%')
            ->get()
            ->toArray();
    }

    /**
     * Cleanup old/unused uploads
     */
    public function cleanup(int $daysOld = 30): int
    {
        $cutoffDate = now()->subDays($daysOld);
        $deletedCount = 0;

        try {
            // Get files older than cutoff
            $files = Storage::disk('uploads')->files('avatars');

            foreach ($files as $file) {
                $lastModified = Storage::disk('uploads')->lastModified($file);

                if ($lastModified < $cutoffDate->timestamp) {
                    Storage::disk('uploads')->delete($file);
                    $deletedCount++;
                }
            }

            return $deletedCount;
        } catch (\Exception $e) {
            report($e);
            return 0;
        }
    }

    /**
     * Get upload statistics
     */
    public function getStatistics(): array
    {
        try {
            $diskUsage = 0;
            $fileCount = 0;

            $files = Storage::disk('uploads')->allFiles();
            foreach ($files as $file) {
                $fileCount++;
                $diskUsage += Storage::disk('uploads')->size($file);
            }

            return [
                'total_files' => $fileCount,
                'total_disk_usage' => $diskUsage,
                'readable_disk_usage' => $this->formatBytes($diskUsage),
                'average_file_size' => $fileCount > 0 ? $diskUsage / $fileCount : 0,
            ];
        } catch (\Exception $e) {
            report($e);
            return [
                'error' => 'Failed to retrieve statistics',
            ];
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
