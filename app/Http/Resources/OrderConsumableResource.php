<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Order\OrderConsumableDetails
 */
class OrderConsumableResource extends JsonResource
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
            'id_order' => $this->id_order,
            'id_consumable' => $this->id_consumable,
            'quantity' => $this->quantity,

            'consumable' => new ConsumableResource($this->whenLoaded('consumable')),
            'order' => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
