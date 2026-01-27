<?php
namespace App\Http\Resources;

use App\Models\Order\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Order\OrderStatusHistory $this */
        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_label' => Order::getStatusLabelByStatus($this->status),
            'comment' => $this->comment,
            'author' => new UserResourceShort($this->author),
            'created_at' => $this->created_at,
        ];
    }
}