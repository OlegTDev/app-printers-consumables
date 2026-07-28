<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Printer\Printer
 */
class PrinterResource extends JsonResource
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
            'vendor' => $this->vendor,
            'model' => $this->model,
            'is_color_print' => $this->is_color_print,
            'author' => new UserResourceShort($this->whenLoaded('author')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'consumables' => $this->whenLoaded('consumables', fn() => ConsumableResource::collection($this->consumables)),
        ];
    }
}
