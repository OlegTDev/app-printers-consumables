<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Order\Order
 */
class OrderResource extends JsonResource
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
            'org_code' => $this->org_code,
            'status' => $this->status,
            'comment' => $this->comment,
            'quantity' => $this->quantity,
            'service_request_number' => $this->service_request_number,
            'service_request_date' => $this->service_request_date,
            'requested' => new UserResourceShort($this->whenLoaded('requested')),
            'created_at' => $this->created_at,
            'updated_at'=> $this->updated_at,

            'organization' => new OrganizationResource($this->whenLoaded('organization')),
        ];
    }
}
