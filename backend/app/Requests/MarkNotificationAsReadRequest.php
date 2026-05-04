<?php

namespace App\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Mark Notification As Read Request Validation
 *
 * Validates marking notification as read
 */
class MarkNotificationAsReadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // No required fields - marking as read needs only the ID from URL
            // This ensures the request was properly formed
        ];
    }
}
