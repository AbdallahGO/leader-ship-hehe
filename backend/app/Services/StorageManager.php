<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Storage Manager
 *
 * Manages file storage, paths, and organization
 * Handles storage configuration and file location management
 */
class StorageManager
{
    /**
     * Storage paths configuration
     */
    private array $paths = [
        'avatars' => 'uploads/avatars',
        'avatars_variants' => 'uploads/avatars/variants',
        'documents' => 'uploads/documents',
        'temp' => 'uploads/temp',
        'archive' => 'uploads/archive',
    ];

    /**
     * Get storage path
     */
    public function getPath(string $type): string
    {
        return $this->paths[$type] ?? 'uploads';
    }

    /**
     * Get full storage path
     */
    public function getFullPath(string $type): string
    {
        return storage_path($this->getPath($type));
    }

    /**
     * Get URL for stored file
     */
    public function getUrl(string $type, string $filename): string
    {
        $path = $this->getPath($type);
        return "/storage/{$path}/{$filename}";
    }

    /**
     * Initialize storage directories
     */
    public function initializeDirectories(): bool
    {
        try {
            foreach ($this->paths as $path) {
                $fullPath = $this->getFullPath($path);
                if (!is_dir($fullPath)) {
                    mkdir($fullPath, 0755, true);
                }
            }

            // Create .htaccess for security
            $this->createHtAccess();

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Create .htaccess for security
     */
    private function createHtAccess(): void
    {
        $htaccess = <<<'EOT'
<FilesMatch "\.php$">
    Deny from all
</FilesMatch>

<FilesMatch "\.phtml$">
    Deny from all
</FilesMatch>

<FilesMatch "\.exe$|\.sh$|\.bat$|\.asp$|\.jsp$">
    Deny from all
</FilesMatch>

# Allow images and documents only
<FilesMatch "\.(jpg|jpeg|png|gif|pdf|doc|docx)$">
    Allow from all
</FilesMatch>
EOT;

        $htaccessPath = storage_path('uploads/.htaccess');
        file_put_contents($htaccessPath, $htaccess);
    }

    /**
     * Get available disk space
     */
    public function getAvailableDiskSpace(): int
    {
        return disk_free_space(storage_path('uploads'));
    }

    /**
     * Get used disk space
     */
    public function getUsedDiskSpace(): int
    {
        $total = 0;
        $files = Storage::allFiles('uploads');

        foreach ($files as $file) {
            $total += Storage::size($file);
        }

        return $total;
    }

    /**
     * Get total disk space
     */
    public function getTotalDiskSpace(): int
    {
        return disk_total_space(storage_path('uploads'));
    }

    /**
     * Get disk usage percentage
     */
    public function getDiskUsagePercentage(): float
    {
        $total = $this->getTotalDiskSpace();
        $used = $this->getUsedDiskSpace();

        return round(($used / $total) * 100, 2);
    }

    /**
     * Create symbolic link for public access
     */
    public function createSymbolicLink(): bool
    {
        try {
            $target = storage_path('uploads');
            $link = public_path('storage');

            if (is_link($link)) {
                unlink($link);
            }

            if (!file_exists($link)) {
                symlink($target, $link);
            }

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Organize user uploads
     */
    public function organizeUserUploads(int $userId): bool
    {
        try {
            $userPath = storage_path("uploads/users/{$userId}");

            if (!is_dir($userPath)) {
                mkdir($userPath, 0755, true);
            }

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Get user storage quota
     */
    public function getUserStorageQuota(int $userId, int $quotaMB = 100): array
    {
        $userPath = "uploads/users/{$userId}";
        $total = 0;

        try {
            if (Storage::exists($userPath)) {
                $files = Storage::files($userPath);
                foreach ($files as $file) {
                    $total += Storage::size($file);
                }
            }

            $quotaBytes = $quotaMB * 1024 * 1024;
            $remaining = max(0, $quotaBytes - $total);

            return [
                'used' => $total,
                'quota' => $quotaBytes,
                'remaining' => $remaining,
                'percentage' => round(($total / $quotaBytes) * 100, 2),
            ];
        } catch (\Exception $e) {
            report($e);
            return ['error' => 'Failed to calculate quota'];
        }
    }

    /**
     * Clean temporary files
     */
    public function cleanTemporaryFiles(int $ageHours = 24): int
    {
        try {
            $tempPath = $this->getPath('temp');
            $deletedCount = 0;

            if (!Storage::exists($tempPath)) {
                return 0;
            }

            $files = Storage::files($tempPath);
            $cutoffTime = now()->subHours($ageHours)->timestamp;

            foreach ($files as $file) {
                $lastModified = Storage::lastModified($file);

                if ($lastModified < $cutoffTime) {
                    Storage::delete($file);
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
     * Archive old files
     */
    public function archiveOldFiles(int $daysOld = 90): int
    {
        try {
            $archivedCount = 0;
            $cutoffDate = now()->subDays($daysOld)->timestamp;

            $files = Storage::allFiles('uploads');

            foreach ($files as $file) {
                if (str_starts_with($file, 'uploads/archive')) {
                    continue;
                }

                $lastModified = Storage::lastModified($file);

                if ($lastModified < $cutoffDate) {
                    $archivePath = str_replace('uploads/', 'uploads/archive/', $file);
                    Storage::move($file, $archivePath);
                    $archivedCount++;
                }
            }

            return $archivedCount;
        } catch (\Exception $e) {
            report($e);
            return 0;
        }
    }

    /**
     * Get storage statistics
     */
    public function getStorageStatistics(): array
    {
        try {
            $total = $this->getTotalDiskSpace();
            $used = $this->getUsedDiskSpace();
            $available = $this->getAvailableDiskSpace();

            return [
                'total' => $total,
                'used' => $used,
                'available' => $available,
                'percentage_used' => round(($used / $total) * 100, 2),
                'readable' => [
                    'total' => $this->formatBytes($total),
                    'used' => $this->formatBytes($used),
                    'available' => $this->formatBytes($available),
                ],
            ];
        } catch (\Exception $e) {
            report($e);
            return ['error' => 'Failed to retrieve storage statistics'];
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Create backup of uploads
     */
    public function createBackup(string $backupPath): bool
    {
        try {
            $sourcePath = storage_path('uploads');
            $timestamp = now()->format('Y-m-d_H-i-s');
            $backupName = "uploads_backup_{$timestamp}.tar.gz";

            // Create tar.gz backup
            exec("cd " . storage_path() . " && tar -czf {$backupPath}/{$backupName} uploads/");

            return file_exists("{$backupPath}/{$backupName}");
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Get all storage paths
     */
    public function getAllPaths(): array
    {
        return $this->paths;
    }
}
