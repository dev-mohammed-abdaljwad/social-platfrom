<?php
namespace App\Http\Requests\Web\Reaction;
use Illuminate\Foundation\Http\FormRequest;

class reactToPost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust authorization logic as needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
                'type' => 'required|string|in:like,love,haha,wow,sad,angry',
        ];
    }
} 