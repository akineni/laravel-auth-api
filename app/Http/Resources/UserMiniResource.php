<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserMiniResource extends JsonResource
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
            'fullname' => $this->fullname,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'avatar' => $this->avatar,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'status' => $this->status,
            'email_verified' => ! is_null($this->email_verified_at),
            'role_names' => $this->whenLoaded(
                'roles',
                fn () => $this->roles->pluck('name')->values()
            ),
            'last_login' => optional($this->last_login)->toDateTimeString(),
            'created_at' => optional($this->created_at)->toDateTimeString(),
        ];
    }
}