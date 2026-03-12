<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing(['roles.permissions']);

        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'gender' => $this->gender,
            'phone_number' => $this->phone_number,
            'roles' => $this->formatRoles(),
            'status' => $this->status,
            'two_fa' => (bool) $this->two_fa,
            'email_verified' => ! is_null($this->email_verified_at),
            'state' => $this->state,
            'country' => $this->country,
            'address' => $this->address,
            'postcode' => $this->postcode,
            'avatar' => $this->avatar,
            'last_login' => optional($this->last_login)->toDateTimeString(),
            'email_verified_at' => optional($this->email_verified_at)->toDateTimeString(),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }

    protected function formatRoles()
    {
        return $this->whenLoaded('roles', function () {
            return $this->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name'),
                ];
            });
        });
    }
}