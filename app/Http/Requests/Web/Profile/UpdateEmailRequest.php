<?php

namespace App\Http\Requests\Web\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already taken.',
            'password.required' => 'Password is required for verification.',
        ];
    }
}
