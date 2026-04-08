<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderConsumableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Order\OrderConsumableDetails $this */
        return [
            'id' => $this->id,
            'id_order' => $this->id_order,
            'id_consumable' => $this->id_consumable,
            'quantity' => $this->quantity,

            'consumable' => new ConsumableResource($this->consumable),
            'order' => new OrderResource($this->order),
        ];
    }
}
