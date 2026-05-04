<?php

namespace App\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Profile Request Validation
 * 
 * Validates user profile update input
 */
class UpdateProfileRequest extends FormRequest
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
        $userId = auth()->id();

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s\-\']+$/',
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                "unique:users,email,{$userId}",
                'max:255',
            ],
            'bio' => [
                'nullable',
                'string',
                'max:500',
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^[\d\s\-\+\(\)]+$/',
                'max:20',
            ],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, and apostrophes.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'bio.max' => 'Bio cannot exceed 500 characters.',
            'phone.regex' => 'Phone number format is invalid.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name ?? ''),
            'email' => trim(strtolower($this->email ?? '')),
            'phone' => preg_replace('/\s+/', '', $this->phone ?? ''),
        ]);
    }
}
