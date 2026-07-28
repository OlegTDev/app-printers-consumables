<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Order\OrderMiscDetails
 */
class OrderMiscResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,

            'order' => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
