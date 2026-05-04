<?php

namespace App\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Upload Avatar Request Validation
 * 
 * Validates avatar upload input
 */
class UploadAvatarRequest extends FormRequest
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
            'avatar' => [
                'required',
                'image',
                'mimes:jpeg,png,gif,webp',
                'max:5120', // 5MB max
                'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000',
            ],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'avatar.required' => 'Avatar file is required.',
            'avatar.image' => 'File must be a valid image.',
            'avatar.mimes' => 'Avatar must be a JPEG, PNG, GIF, or WebP image.',
            'avatar.max' => 'Avatar file size cannot exceed 5MB.',
            'avatar.dimensions' => 'Avatar dimensions must be between 100x100 and 4000x4000 pixels.',
        ];
    }
}
