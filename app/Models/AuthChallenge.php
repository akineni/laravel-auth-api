<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $user_id
 * @property string $challenge_token
 * @property string|null $code
 * @property string $method Verification mechanism used for this challenge (otp_email, otp_sms, totp, passkey, push, etc).
 * @property string $context Purpose of the challenge (login, email_verification, password_reset, disable_two_fa, etc).
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthChallenge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthChallenge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthChallenge query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthChallenge whereChallengeToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthChallenge whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthChallenge whereContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthChallenge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthChallenge whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthChallenge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthChallenge whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthChallenge whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthChallenge whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuthChallenge whereVerifiedAt($value)
 * @mixin \Eloquent
 */
class AuthChallenge extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
