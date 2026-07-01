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
            'organizations' => OrganizationResource::collection($this->organizations),
        ];
    }
}
