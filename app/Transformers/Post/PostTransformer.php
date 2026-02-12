<?php

namespace App\Transformers\Post;

use App\Transformers\User\UserTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class PostTransformer extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'image' => $this->image,
            'video' => $this->video,
            'location' => $this->location,
            'privacy' => $this->privacy?->value,
            'type' => $this->type?->value,
            'user' => new UserTransformer($this->whenLoaded('user')),
            'likes_count' => $this->when(isset($this->likes_count), $this->likes_count),
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
            'is_liked' => $this->when(isset($this->is_liked), $this->is_liked),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}