<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Consumable\ConsumableCount
 */
class ConsumableCountResource extends JsonResource
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
            'id_consumable' => $this->id_consumable,
            'count' => $this->count,
            'organizations' => $this->whenLoaded('organizations', fn() => OrganizationResource::collection($this->whenLoaded('organizations'))),
            'consumable' => $this->whenLoaded('consumable', fn() => ConsumableResource::make($this->consumable)),
            'created_at' => $this->created_at,
            'updated_at'=> $this->updated_at,
        ];
    }
}
