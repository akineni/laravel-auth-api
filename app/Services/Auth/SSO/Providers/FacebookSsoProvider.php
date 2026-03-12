<?php

namespace App\Services\Auth\SSO\Providers;

use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Services\Auth\SSO\Contracts\SsoProviderInterface;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FacebookSsoProvider implements SsoProviderInterface
{
    public function redirect(string $state): RedirectResponse
    {
        return Socialite::driver('facebook')
            ->stateless()
            ->with(['state' => $state])
            ->redirect();
    }

    public function handleCallback(): User
    {
        $facebookUser = Socialite::driver('facebook')
            ->stateless()
            ->user();

        return $this->persistUser($facebookUser);
    }

    protected function persistUser(SocialiteUser $facebookUser): User
    {
        $user = User::withTrashed()->firstOrNew([
            'email' => $facebookUser->getEmail(),
        ]);

        if ($user->exists && $user->trashed()) {
            $user->restore();
        }

        $user->firstname = $user->firstname ?: ($facebookUser->getName() ?? 'Facebook');
        $user->lastname = $user->lastname ?: 'User';
        $user->avatar = $facebookUser->getAvatar() ?? $user->avatar;
        $user->email_verified_at = now();
        $user->status = UserStatusEnum::ACTIVE->value;
        $user->last_login = now();

        if (!$user->exists) {
            $user->password = Str::random(32);
        }

        $user->save();

        return $user;
    }
}