<?php

namespace App\Http\Resources;

use App\Enums\TwoFactorMethodEnum;
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
            'signup_source' => $this->signup_source,
            'gender' => $this->gender,
            'phone_number' => $this->phone_number,
            'roles' => $this->formatRoles(),
            'status' => $this->status,
            'two_fa' => (bool) $this->two_fa,
            'two_fa_method' => $this->two_fa_method,
            'authenticator_configured' => $this->two_fa_method === TwoFactorMethodEnum::AUTHENTICATOR_APP->value && ! is_null($this->two_fa_secret),
            'email_verified' => ! is_null($this->email_verified_at),
            'phone_verified' => ! is_null($this->phone_verified_at),
            'state' => $this->state,
            'country' => $this->country,
            'address' => $this->address,
            'postcode' => $this->postcode,
            'avatar' => $this->avatar,
            'last_login' => optional($this->last_login)->toDateTimeString(),
            'email_verified_at' => optional($this->email_verified_at)->toDateTimeString(),
            'phone_verified_at' => optional($this->phone_verified_at)->toDateTimeString(),
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