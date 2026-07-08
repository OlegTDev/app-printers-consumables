<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Consumable\ConsumableCountAdded
 */
class ConsumableCountAddedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_consumable_count' => $this->id_consumable_count,
            'count' => $this->count,
            'author' => new UserResourceShort($this->whenLoaded('author')),
            'created_at' => $this->created_at,
            'updated_at'=> $this->updated_at,
        ];
    }
}
