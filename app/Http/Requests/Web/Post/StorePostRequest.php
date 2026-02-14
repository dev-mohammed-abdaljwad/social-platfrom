<?php

namespace App\Http\Requests\Web\Post;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'], // 10MB max
            'video' => ['nullable', 'mimetypes:video/mp4,video/mpeg,video/quicktime,video/webm', 'max:102400'], // 100MB max
            'location' => ['nullable', 'string', 'max:255'],
            'privacy' => ['nullable', 'in:public,friends,private'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.max' => 'Post content cannot exceed 5000 characters.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Only jpeg, png, jpg, gif, and webp images are allowed.',
            'image.max' => 'Image size cannot exceed 10MB.',
            'video.mimetypes' => 'Only mp4, mpeg, quicktime, and webm videos are allowed.',
            'video.max' => 'Video size cannot exceed 100MB.',
            'privacy.in' => 'Privacy setting must be public, friends, or private.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (empty($this->content) && !$this->hasFile('image') && !$this->hasFile('video')) {
                $validator->errors()->add('content', 'Please provide text, image, or video for your post.');
            }
        });
    }
}
