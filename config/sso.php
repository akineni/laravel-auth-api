<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SSO Providers
    |--------------------------------------------------------------------------
    |
    | Mapping of provider identifiers to their service implementations.
    |
    */

    'providers' => [

        'google' => \App\Services\Auth\SSO\Providers\GoogleSsoProvider::class,
        'facebook' => \App\Services\Auth\SSO\Providers\FacebookSsoProvider::class,

        // Future providers
        // 'github' => \App\Services\Auth\SSO\Providers\GithubSsoProvider::class,
        // 'apple'  => \App\Services\Auth\SSO\Providers\AppleSsoProvider::class,

    ],

];