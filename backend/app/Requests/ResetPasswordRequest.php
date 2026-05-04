<?php

namespace App\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Reset Password Request Validation
 *
 * Validates password reset submission
 */
class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc',
                'exists:users,email',
                'max:255',
            ],
            'token' => [
                'required',
                'string',
                'min:32',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]+$/',
            ],
            'password_confirmation' => [
                'required',
                'string',
                'same:password',
            ],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.exists' => 'Email address not found in our system.',
            'token.required' => 'Reset token is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.regex' => 'Password must contain uppercase, lowercase, numbers, and special characters.',
            'password_confirmation.same' => 'Password confirmation does not match.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => trim(strtolower($this->email ?? '')),
        ]);
    }
}
