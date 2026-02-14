<?php

namespace App\Http\Requests\Web\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username,' . $userId],
            'bio' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'username.alpha_dash' => 'Username can only contain letters, numbers, dashes and underscores.',
        ];
    }
}
