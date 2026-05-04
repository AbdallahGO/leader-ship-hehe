<?php

namespace App\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Change Password Request Validation
 * 
 * Validates password change input
 */
class ChangePasswordRequest extends FormRequest
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
            'current_password' => [
                'required',
                'string',
                'min:8',
            ],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'different:current_password',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]+$/',
            ],
            'new_password_confirmation' => [
                'required',
                'string',
                'same:new_password',
            ],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 8 characters long.',
            'new_password.different' => 'New password must be different from current password.',
            'new_password.regex' => 'Password must contain uppercase, lowercase, numbers, and special characters.',
            'new_password_confirmation.same' => 'Password confirmation does not match.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verify current password
            if (!Hash::check($this->current_password, auth()->user()->password)) {
                $validator->errors()->add(
                    'current_password',
                    'Current password is incorrect.'
                );
            }
        });

        return $validator;
    }
}
