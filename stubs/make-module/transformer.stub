<?php

namespace App\Transformers\\{$name};

use Illuminate\Http\Resources\Json\JsonResource;

class {$name}Transformer extends JsonResource
{
    public function toArray(\$request): array
    {
        return [
            'id' => \$this->id,
            // Add more fields
            'created_at' => \$this->created_at?->toISOString(),
            'updated_at' => \$this->updated_at?->toISOString(),
        ];
    }
}