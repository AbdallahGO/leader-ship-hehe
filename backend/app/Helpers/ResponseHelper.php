<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

/**
 * Response Helper
 * 
 * Provides consistent JSON response format across all API endpoints.
 * Ensures standardized structure for success and error responses.
 */
class ResponseHelper
{
    /**
     * Success response wrapper
     * 
     * @param mixed $data The response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code (default 200)
     * 
     * @return JsonResponse
     */
    public static function success($data = null, string $message = 'Request successful', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.api_version', 'v1'),
        ], $statusCode);
    }

    /**
     * Error response wrapper
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code (default 400)
     * @param array $errors Additional error details
     * 
     * @return JsonResponse
     */
    public static function error(string $message = 'An error occurred', int $statusCode = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors ?: null,
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.api_version', 'v1'),
        ], $statusCode);
    }

    /**
     * Paginated response wrapper
     * 
     * @param mixed $data Paginated data (from paginate())
     * @param string $message Success message
     * @param int $statusCode HTTP status code (default 200)
     * 
     * @return JsonResponse
     */
    public static function paginated($data, string $message = 'Request successful', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'total_pages' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ],
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.api_version', 'v1'),
        ], $statusCode);
    }

    /**
     * Not Found response
     * 
     * @param string $message Error message
     * 
     * @return JsonResponse
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return static::error($message, 404);
    }

    /**
     * Unauthorized response
     * 
     * @param string $message Error message
     * 
     * @return JsonResponse
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return static::error($message, 401);
    }

    /**
     * Forbidden response
     * 
     * @param string $message Error message
     * 
     * @return JsonResponse
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return static::error($message, 403);
    }

    /**
     * Validation error response
     * 
     * @param array $errors Validation errors
     * 
     * @return JsonResponse
     */
    public static function validationError(array $errors = []): JsonResponse
    {
        return static::error('Validation failed', 422, $errors);
    }

    /**
     * Conflict response (resource already exists)
     * 
     * @param string $message Error message
     * 
     * @return JsonResponse
     */
    public static function conflict(string $message = 'Resource already exists'): JsonResponse
    {
        return static::error($message, 409);
    }

    /**
     * Server error response
     * 
     * @param string $message Error message
     * 
     * @return JsonResponse
     */
    public static function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return static::error($message, 500);
    }
}
