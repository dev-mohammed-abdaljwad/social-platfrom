<?php

namespace App\Transformers\Like;

use App\Transformers\User\UserTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class LikeTransformer extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserTransformer($this->whenLoaded('user')),
            'likeable_type' => class_basename($this->likeable_type),
            'likeable_id' => $this->likeable_id,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
