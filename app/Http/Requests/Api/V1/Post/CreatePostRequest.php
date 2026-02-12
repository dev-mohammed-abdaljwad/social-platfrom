<?php

namespace App\Http\Requests\Api\V1\Post;

use App\Enums\ContentTypeEnum;
use App\Enums\PrivacyTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'string', 'max:255'],
            'video' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'privacy' => ['nullable', Rule::in(PrivacyTypeEnum::getValues())],
            'type' => ['nullable', Rule::in(ContentTypeEnum::getValues())],
        ];
    }
}
