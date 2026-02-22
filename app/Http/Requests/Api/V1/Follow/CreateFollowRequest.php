<?php

namespace App\Http\Requests\Api\V1\Follow;

use Illuminate\Foundation\Http\FormRequest;

class CreateFollowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // add validation rules
        ];
    }
}
