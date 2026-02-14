<?php

namespace App\Http\Requests\Web\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoverPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'cover_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'], // 10MB max
        ];
    }

    public function messages(): array
    {
        return [
            'cover_photo.required' => 'Please select an image.',
            'cover_photo.image' => 'The file must be an image.',
            'cover_photo.mimes' => 'Only jpeg, png, jpg, gif, and webp images are allowed.',
            'cover_photo.max' => 'Image size cannot exceed 10MB.',
        ];
    }
}
