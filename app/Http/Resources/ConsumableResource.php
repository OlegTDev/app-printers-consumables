<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsumableResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Consumable\Consumable $this */
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'color' => $this->color,
            'description' => $this->description,
            'arch' => $this->arch,
            'author' => new UserResourceShort($this->author),
            'created_at' => $this->created_at,
            'updated_at'=> $this->updated_at,
        ];
    }
}
