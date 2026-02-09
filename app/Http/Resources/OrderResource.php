<?php

namespace App\Http\Resources;

use App\Models\Order\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Order\Order $this */
        return [
            'id' => $this->id,
            'org_code' => $this->org_code,
            'status' => $this->status,
            'status_label' => Order::getStatusLabelByStatus($this->status),
            'comment' => $this->comment,
            'quantity' => $this->quantity,
            'service_request_number' => $this->service_request_number,
            'service_request_date' => $this->service_request_date,
            'requested' => new UserResourceShort($this->requested),
            'created_at' => $this->created_at,
            'updated_at'=> $this->updated_at,

            'organization' => new OrganizationResource($this->organization),
        ];
    }
}
