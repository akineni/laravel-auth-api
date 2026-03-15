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

/**
 * @property string $id
 * @property string $firstname
 * @property string $lastname
 * @property string|null $avatar
 * @property string|null $gender
 * @property string|null $phone_number
 * @property string|null $postcode
 * @property string|null $state
 * @property string|null $country
 * @property string|null $address
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $signup_source Account origin: self, admin, seeder, google, facebook, etc
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $last_login
 * @property string $status User lifecycle status
 * @property bool $two_fa Whether 2FA is enabled
 * @property string $two_fa_method Preferred 2FA method
 * @property string|null $two_fa_secret Encrypted authenticator app secret
 * @property \Illuminate\Support\Carbon|null $two_fa_confirmed_at When authenticator app setup was confirmed
 * @property array<array-key, mixed>|null $two_fa_recovery_codes Encrypted JSON recovery codes
 * @property int|null $two_fa_last_used_window Last accepted TOTP window to prevent code replay
 * @property int $failed_logins
 * @property \Illuminate\Support\Carbon|null $locked_until
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $fullname
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static Builder<static>|User applyFilters(array $filters = [], ?array $searchFields = null)
 * @method static Builder<static>|User createdBetween(?string $startDate = null, ?string $endDate = null, string $column = 'created_at')
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder<static>|User filterStatus(?mixed $status)
 * @method static Builder<static>|User forActionModule(?string $actionModule)
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User onlyTrashed()
 * @method static Builder<static>|User permission($permissions, $without = false)
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static Builder<static>|User search(?string $term, ?array $fields = null)
 * @method static Builder<static>|User whereAddress($value)
 * @method static Builder<static>|User whereAvatar($value)
 * @method static Builder<static>|User whereCountry($value)
 * @method static Builder<static>|User whereCreatedAt($value)
 * @method static Builder<static>|User whereDeletedAt($value)
 * @method static Builder<static>|User whereEmail($value)
 * @method static Builder<static>|User whereEmailVerifiedAt($value)
 * @method static Builder<static>|User whereFailedLogins($value)
 * @method static Builder<static>|User whereFirstname($value)
 * @method static Builder<static>|User whereGender($value)
 * @method static Builder<static>|User whereId($value)
 * @method static Builder<static>|User whereLastLogin($value)
 * @method static Builder<static>|User whereLastname($value)
 * @method static Builder<static>|User whereLockedUntil($value)
 * @method static Builder<static>|User wherePassword($value)
 * @method static Builder<static>|User wherePhoneNumber($value)
 * @method static Builder<static>|User wherePostcode($value)
 * @method static Builder<static>|User whereRememberToken($value)
 * @method static Builder<static>|User whereSignupSource($value)
 * @method static Builder<static>|User whereState($value)
 * @method static Builder<static>|User whereStatus($value)
 * @method static Builder<static>|User whereTwoFa($value)
 * @method static Builder<static>|User whereTwoFaConfirmedAt($value)
 * @method static Builder<static>|User whereTwoFaLastUsedWindow($value)
 * @method static Builder<static>|User whereTwoFaMethod($value)
 * @method static Builder<static>|User whereTwoFaRecoveryCodes($value)
 * @method static Builder<static>|User whereTwoFaSecret($value)
 * @method static Builder<static>|User whereUpdatedAt($value)
 * @method static Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|User withoutPermission($permissions)
 * @method static Builder<static>|User withoutRole($roles, $guard = null)
 * @method static Builder<static>|User withoutTrashed()
 * @mixin \Eloquent
 */
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
            'locked_until' => 'datetime',
            'two_fa' => 'boolean',
            'two_fa_secret' => 'encrypted',
            'two_fa_recovery_codes' => 'encrypted:array',
            'two_fa_confirmed_at' => 'datetime',
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