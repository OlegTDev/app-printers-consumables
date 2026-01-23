<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResourceShort extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Auth\User $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'fio' => $this->fio,
            'department' => $this->department,
            'post' => $this->post,
        ];
    }
}
