<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Printer\PrinterWorkplace
 */
class PrinterWorkplaceResource extends JsonResource
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
            'id_printer' => $this->id_printer,
            'org_code' => $this->org_code,
            'location' => $this->location,
            'serial_number' => $this->serial_number,
            'inventory_number' => $this->inventory_number,
            'created_at' => $this->created_at,
            'updated_at'=> $this->updated_at,

            'printer' => new PrinterResource($this->whenLoaded('printer')),
            'organization' => $this->whenLoaded('organization', fn() => OrganizationResource::make($this->organization)),
            'author' => $this->whenLoaded('author', fn() => UserResourceShort::make($this->author)),
        ];
    }
}
