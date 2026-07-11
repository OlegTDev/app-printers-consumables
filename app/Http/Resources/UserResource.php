<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

/**
 * @mixin \App\Models\Auth\User
 */
class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'org_code' => $this->org_code,
            'fio' => $this->fio,
            'department' => $this->department,
            'lotus_mail' => $this->lotus_mail,
            'telephone' => $this->telephone,
            'photo' => $this->photo_path ? URL::route('image', ['path' => $this->photo_path, 'w' => 40, 'h' => 40, 'fit' => 'crop']) : null,
            'deleted_at' => $this->deleted_at,

            'roles' => $this->whenLoaded('roles', fn() => RoleResource::collection($this->roles)),
            'organizations' => $this->whenLoaded('organizations', fn() => OrganizationResource::collection($this->organizations)),
        ];
    }
}
