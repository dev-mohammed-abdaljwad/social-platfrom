<?php

namespace App\Http\Requests\Web\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilePictureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'profile_picture' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // 5MB max
        ];
    }

    public function messages(): array
    {
        return [
            'profile_picture.required' => 'Please select an image.',
            'profile_picture.image' => 'The file must be an image.',
            'profile_picture.mimes' => 'Only jpeg, png, jpg, gif, and webp images are allowed.',
            'profile_picture.max' => 'Image size cannot exceed 5MB.',
        ];
    }
}
