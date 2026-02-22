<?php

namespace App\Transformers\Follow;

use App\Transformers\User\UserTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class FollowTransformer extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'status'     => $this->status->value,
            'follower'   => new UserTransformer($this->whenLoaded('follower')),
            'followee'   => new UserTransformer($this->whenLoaded('followee')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
