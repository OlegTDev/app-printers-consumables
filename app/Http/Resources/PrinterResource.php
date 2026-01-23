<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrinterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Printer\Printer $this */
        return [
            'id' => $this->id,
            'vendor' => $this->vendor,
            'model' => $this->model,
            'is_color_print' => $this->is_color_print,
            'author' => new UserResourceShort($this->author),
        ];
    }
}
