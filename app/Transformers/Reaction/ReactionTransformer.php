<?php

namespace App\Transformers\Reaction;

use Illuminate\Http\Resources\Json\JsonResource;

class ReactionTransformer extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            // Add more fields
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}