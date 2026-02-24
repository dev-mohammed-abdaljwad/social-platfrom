<?php

namespace App\Http\Requests\Api\V1\Mentions;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMentionsRequest extends FormRequest
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
