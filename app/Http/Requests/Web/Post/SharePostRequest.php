<?php

namespace App\Http\Requests\Web\Post;

use Illuminate\Foundation\Http\FormRequest;

class SharePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.max' => 'Share content cannot exceed 1000 characters.',
        ];
    }
}
