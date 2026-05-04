<?php

namespace App\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update User Request Validation (Admin)
 *
 * Validates admin user update operations
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s\-\']+$/',
            ],
            'email' => [
                'sometimes',
                'required',
                'email:rfc,dns',
                "unique:users,email,{$userId}",
                'max:255',
            ],
            'role' => [
                'sometimes',
                'required',
                'string',
                'in:user,admin,moderator',
            ],
            'is_active' => [
                'sometimes',
                'required',
                'boolean',
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
            'role.in' => 'Role must be user, admin, or moderator.',
            'is_active.boolean' => 'Active status must be true or false.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->name ? trim($this->name) : null,
            'email' => $this->email ? trim(strtolower($this->email)) : null,
        ]);
    }
}
