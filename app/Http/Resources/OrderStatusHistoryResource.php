<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Order\OrderStatusHistory
 */
class OrderStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'comment' => $this->comment,
            'author' => new UserResourceShort($this->whenLoaded('author')),
            'created_at' => $this->created_at,
        ];
    }
}
