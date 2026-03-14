<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Concerns\HasCommonFilterScopes;
use App\Models\Concerns\HasSearchScope;
use App\Traits\HasFullName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory,
        Notifiable,
        HasRoles,
        HasApiTokens,
        HasUuids,
        SoftDeletes,
        HasFullName,
        HasSearchScope,
        HasCommonFilterScopes;

    protected $guard_name = 'api';

    /**
     * The attributes that are not mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'failed_logins',
        'locked_until',
        'mfa_secret',
        'password',
        'remember_token',
        'otp_code',
        'otp_expires_at',
        'two_fa_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_login' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_fa' => 'boolean',
            'locked_until' => 'datetime',
        ];
    }

    /**
     * Searchable real columns.
     */
    public function getSearchableColumns(): array
    {
        return [
            'firstname',
            'lastname',
            'email',
            'phone_number',
            'status',
            'gender',
            'state',
            'country',
            'postcode',
        ];
    }

    /**
     * Searchable aliases / virtual fields.
     */
    public function getSearchableAliases(): array
    {
        return [
            'fullname' => function (Builder $query, string $term) {
                $query->where(function (Builder $q) use ($term) {
                    $q->orWhere('firstname', 'LIKE', "%{$term}%")
                        ->orWhere('lastname', 'LIKE', "%{$term}%")
                        ->orWhereRaw(
                            "CONCAT(firstname, ' ', lastname) LIKE ?",
                            ["%{$term}%"]
                        )
                        ->orWhereRaw(
                            "CONCAT(lastname, ' ', firstname) LIKE ?",
                            ["%{$term}%"]
                        );
                });
            },
        ];
    }

    /**
     * Allowed searchable relations.
     */
    public function getSearchableRelations(): array
    {
        return [
            'roles',
        ];
    }

    /**
     * Default search fields when frontend doesn't pass searchable fields.
     */
    public function getDefaultSearchFields(): array
    {
        return [
            'fullname',
            'email',
            'phone_number',
            'status',
            'roles.name',
        ];
    }

    /**
     * Use the actual lifecycle status column.
     */
    protected function getStatusFilterColumn(): ?string
    {
        return 'status';
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}