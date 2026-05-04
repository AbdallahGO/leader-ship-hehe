<?php

/**
 * File Upload Configuration
 *
 * Configuration for file uploads, validation, and optimization
 */

return [
    /**
     * Storage Configuration
     */
    'storage' => [
        'disk' => env('UPLOAD_DISK', 'local'),
        'path' => 'uploads',
    ],

    /**
     * File Size Limits (in KB)
     */
    'max_size' => (int)env('MAX_UPLOAD_SIZE', 5120), // 5MB default

    /**
     * Image Upload Configuration
     */
    'image' => [
        'min_width' => 100,
        'max_width' => 4000,
        'min_height' => 100,
        'max_height' => 4000,
        'min_size_kb' => 10,
        'max_size_kb' => (int)env('MAX_UPLOAD_SIZE', 5120),
    ],

    /**
     * Avatar Upload Configuration
     */
    'avatar' => [
        'path' => 'avatars',
        'variants_path' => 'avatars/variants',
        'max_size_kb' => 5120,
        'generate_variants' => true,
        'variants' => [
            'thumb' => ['width' => 150, 'height' => 150],
            'medium' => ['width' => 300, 'height' => 300],
            'large' => ['width' => 800, 'height' => 800],
        ],
    ],

    /**
     * Document Upload Configuration
     */
    'document' => [
        'path' => 'documents',
        'max_size_kb' => 10240, // 10MB
        'allowed_extensions' => ['pdf', 'doc', 'docx', 'xlsx', 'txt'],
        'allowed_mimes' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
        ],
    ],

    /**
     * Video Upload Configuration
     */
    'video' => [
        'path' => 'videos',
        'max_size_kb' => 102400, // 100MB
        'allowed_extensions' => ['mp4', 'avi', 'mov', 'webm'],
        'allowed_mimes' => [
            'video/mp4',
            'video/x-msvideo',
            'video/quicktime',
            'video/webm',
        ],
    ],

    /**
     * Allowed File Types
     */
    'allowed_types' => [
        'avatar' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        ],
        'document' => [
            'extensions' => ['pdf', 'doc', 'docx', 'xlsx', 'txt'],
            'mimes' => ['application/pdf', 'application/msword', 'text/plain'],
        ],
        'video' => [
            'extensions' => ['mp4', 'avi', 'mov', 'webm'],
            'mimes' => ['video/mp4', 'video/x-msvideo', 'video/quicktime', 'video/webm'],
        ],
    ],

    /**
     * Image Optimization
     */
    'optimization' => [
        'enabled' => true,
        'quality' => [
            'jpg' => 85,
            'jpeg' => 85,
            'png' => 9,
            'webp' => 85,
        ],
        'resize_large_images' => true,
        'max_resize_width' => 4000,
        'max_resize_height' => 4000,
        'generate_webp' => false,
    ],

    /**
     * Malware Scanning
     */
    'scanning' => [
        'enabled' => (bool)env('ENABLE_FILE_SCANNING', false),
        'provider' => env('FILE_SCAN_PROVIDER', 'clamav'),
        'timeout' => 30,
    ],

    /**
     * Cleanup Configuration
     */
    'cleanup' => [
        'enabled' => true,
        'delete_orphaned_days' => 30,
        'archive_old_days' => 90,
        'cleanup_temp_hours' => 24,
    ],

    /**
     * User Storage Quota
     */
    'quota' => [
        'enabled' => true,
        'default_mb' => 100,
        'premium_mb' => 1000,
    ],

    /**
     * Public Access
     */
    'public' => [
        'avatars' => true,
        'documents' => false,
        'videos' => false,
    ],

    /**
     * Versioning
     */
    'versioning' => [
        'enabled' => false,
        'keep_versions' => 3,
    ],

    /**
     * Logging
     */
    'logging' => [
        'enabled' => true,
        'log_uploads' => true,
        'log_downloads' => true,
        'log_deletions' => true,
    ],
];
