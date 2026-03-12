<?php

namespace App\Services\Auth\SSO\Providers;

use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Services\Auth\SSO\Contracts\SsoProviderInterface;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleSsoProvider implements SsoProviderInterface
{
    public function redirect(string $state): RedirectResponse
    {
        return Socialite::driver('google')
            ->stateless()
            ->with(['state' => $state])
            ->redirect();
    }

    public function handleCallback(): User
    {
        $googleUser = $this->getGoogleUser();

        return $this->persistUser($googleUser);
    }

    protected function getGoogleUser(): SocialiteUser
    {
        return Socialite::driver('google')
            ->stateless()
            ->user();
    }

    protected function persistUser(SocialiteUser $googleUser): User
    {
        $googleData = $googleUser->user;

        $user = User::withTrashed()->firstOrNew([
            'email' => $googleUser->getEmail(),
        ]);

        if ($user->exists && $user->trashed()) {
            $user->restore();
        }

        $user->firstname = $user->firstname ?: ($googleData['given_name'] ?? 'Google');
        $user->lastname = $user->lastname ?: ($googleData['family_name'] ?? 'User');
        $user->avatar = $googleData['picture'] ?? $user->avatar;
        $user->email_verified_at = !empty($googleData['email_verified'])
            ? ($user->email_verified_at ?? now())
            : $user->email_verified_at;
        $user->status = UserStatusEnum::ACTIVE->value;
        $user->last_login = now();

        if (!$user->exists) {
            $user->password = Str::random(32);
        }

        $user->save();

        return $user;
    }
}