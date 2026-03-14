<?php

namespace App\Services\Auth\SSO\Providers;

use App\Enums\SignupSourceEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Services\Auth\SSO\Contracts\SsoProviderInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FacebookSsoProvider implements SsoProviderInterface
{
    /**
     * Redirect user to Facebook OAuth
     */
    public function redirect(string $state): RedirectResponse
    {
        return Socialite::driver('facebook')
            ->stateless()
            ->scopes(['email'])
            ->fields([
                'id',
                'first_name',
                'last_name',
                'name',
                'email',
                'picture.type(large)'
            ])
            ->with(['state' => $state])
            ->redirect();
    }

    /**
     * Handle OAuth callback
     */
    public function handleCallback(): User
    {
        $facebookUser = Socialite::driver('facebook')
            ->stateless()
            ->user();

        return $this->persistUser($facebookUser);
    }

    /**
     * Persist or update user
     */
    protected function persistUser(SocialiteUser $facebookUser): User
    {
        $email = $facebookUser->getEmail();

        if (!$email) {
            throw new RuntimeException('Facebook account does not provide an email address.');
        }

        [$firstName, $lastName] = $this->extractNames($facebookUser);

        $user = User::withTrashed()->firstOrNew([
            'email' => $email,
        ]);

        if ($user->exists && $user->trashed()) {
            $user->restore();
        }

        $user->firstname = $user->firstname ?: $firstName;
        $user->lastname = $user->lastname ?: $lastName;
        $user->avatar = $facebookUser->getAvatar() ?: $user->avatar;

        $user->email_verified_at = $user->email_verified_at ?: now();
        $user->signup_source = SignupSourceEnum::FACEBOOK->value;
        $user->status = UserStatusEnum::ACTIVE->value;
        $user->last_login = now();

        if (!$user->exists) {
            $user->password = bcrypt(Str::random(32));
        }

        $user->save();

        return $user;
    }

    /**
     * Extract firstname and lastname
     */
    protected function extractNames(SocialiteUser $facebookUser): array
    {
        $rawUser = (array) ($facebookUser->user ?? []);
        $attributes = (array) ($facebookUser->attributes ?? []);

        $firstName = Arr::get($rawUser, 'first_name')
            ?? Arr::get($attributes, 'first_name');

        $lastName = Arr::get($rawUser, 'last_name')
            ?? Arr::get($attributes, 'last_name');

        if ($firstName || $lastName) {
            return [
                $firstName ?: 'Facebook',
                $lastName ?: 'User',
            ];
        }

        return $this->splitFullName($facebookUser->getName());
    }

    /**
     * Fallback: split full name
     */
    protected function splitFullName(?string $fullName): array
    {
        $fullName = trim((string) $fullName);

        if ($fullName === '') {
            return ['Facebook', 'User'];
        }

        $parts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);

        if (!$parts) {
            return ['Facebook', 'User'];
        }

        if (count($parts) === 1) {
            return [$parts[0], 'User'];
        }

        $firstName = array_shift($parts);

        return [$firstName, implode(' ', $parts)];
    }
}