<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Consumable\ConsumableCountInstalled
 */
class ConsumableCountInstalledResource extends JsonResource
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
            'count' => $this->count,
            'author' => new UserResourceShort($this->whenLoaded('author')),
            'consumableCount' => $this->whenNotNull(
                ConsumableCountResource::make($this->whenLoaded('consumableCount')),
            ),
            'printerWorkplace' => $this->whenNotNull(
                PrinterWorkplaceResource::make($this->whenLoaded('printerWorkplace')),
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
