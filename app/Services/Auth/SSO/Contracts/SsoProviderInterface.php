<?php

namespace App\Services\Auth\SSO\Contracts;

use App\Models\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

interface SsoProviderInterface
{
    /**
     * Redirect the user to the provider authentication page.
     */
    public function redirect(string $state): RedirectResponse;

    /**
     * Handle the provider callback and resolve the local user.
     */
    public function handleCallback(): User;
}