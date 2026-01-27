<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderSparePartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Order\OrderSparePartDetails $this */
        return [
            'id' => $this->id,
            'id_order' => $this->id_order,
            'id_printers_workplace' => $this->id_printers_workplace,
            'id_spare_part' => $this->id_spare_part,
            'call_specialist' => $this->call_specialist,

            'sparePart' => new SparePartsResource($this->sparePart),
            'printerWorkplace' => new PrinterWorkplaceResource($this->printerWorkplace),
            'order' => new OrderResource($this->order),
            'files' => OrderSparePartFileResource::collection($this->files),
        ];
    }
}
