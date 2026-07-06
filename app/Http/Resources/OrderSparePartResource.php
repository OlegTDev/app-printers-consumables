<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Order\OrderSparePartDetails
 */
class OrderSparePartResource extends JsonResource
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
            'id_printers_workplace' => $this->id_printers_workplace,
            'id_spare_part' => $this->id_spare_part,
            'call_specialist' => $this->call_specialist,

            'sparePart' => new ConsumableResource($this->whenLoaded('sparePart')),
            'printerWorkplace' => new PrinterWorkplaceResource($this->whenLoaded('printerWorkplace')),
            'order' => new OrderResource($this->whenLoaded('order')),
            'files' => OrderSparePartFileResource::collection($this->whenLoaded('files')),
        ];
    }
}
