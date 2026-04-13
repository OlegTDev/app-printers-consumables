<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderMiscResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Order\OrderMiscDetails $this */
        return [
            'id' => $this->id,
            'id_order' => $this->id_order,
            'name' => $this->name,
            'description' => $this->description,

            'order' => new OrderResource($this->order),
        ];
    }
}
