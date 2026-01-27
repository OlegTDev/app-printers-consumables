<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderSparePartFileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Order\OrderSparePartDetailsFile $this */
        return [
            'id' => $this->id,
            'id_spare_part_order_detail' => $this->id_spare_part_order_detail,
            'basename'=> basename($this->filename),
            'filename' => $this->filename,
            'url_file_download' => route('download', ['path' => $this->filename]),
        ];
    }
}
