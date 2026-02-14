<?php

namespace App\Http\Requests\Web\Post;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'privacy' => ['nullable', 'in:public,friends,private'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.max' => 'Post content cannot exceed 5000 characters.',
            'privacy.in' => 'Privacy setting must be public, friends, or private.',
        ];
    }
}
