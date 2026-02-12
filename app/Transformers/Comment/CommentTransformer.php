<?php

namespace App\Transformers\Comment;

use App\Transformers\User\UserTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentTransformer extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'post_id' => $this->post_id,
            'parent_id' => $this->parent_id,
            'user' => new UserTransformer($this->whenLoaded('user')),
            'replies' => CommentTransformer::collection($this->whenLoaded('replies')),
            'likes_count' => $this->when(isset($this->likes_count), $this->likes_count),
            'replies_count' => $this->when(isset($this->replies_count), $this->replies_count),
            'is_reply' => $this->parent_id !== null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
