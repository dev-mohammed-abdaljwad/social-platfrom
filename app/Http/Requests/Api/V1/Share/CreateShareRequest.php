<?php

namespace App\Http\Requests\Api\V1\Share;

use Illuminate\Foundation\Http\FormRequest;

class CreateShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
