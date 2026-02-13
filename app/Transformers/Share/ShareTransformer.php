<?php

namespace App\Transformers\Share;

use App\Transformers\Post\PostTransformer;
use App\Transformers\User\UserTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareTransformer extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'user' => new UserTransformer($this->whenLoaded('user')),
            'post' => new PostTransformer($this->whenLoaded('post')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
