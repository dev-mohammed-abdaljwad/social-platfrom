<?php

namespace App\Transformers\Friendship;

use App\Transformers\User\UserTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendshipTransformer extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sender' => new UserTransformer($this->whenLoaded('sender')),
            'receiver' => new UserTransformer($this->whenLoaded('receiver')),
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
