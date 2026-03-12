<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inactivity Timeout
    |--------------------------------------------------------------------------
    | The maximum minutes of inactivity allowed before a user is automatically
    | logged out. This is checked against the token's last activity timestamp.
    |
    */
    'inactivity_timeout' => env('SESSION_INACTIVITY_TIMEOUT_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Token Lifetime
    |--------------------------------------------------------------------------
    | The number of minutes a session token remains valid before it must
    | be refreshed. If expired, the user must log in again.
    |
    */
    'token_lifetime' => env('SESSION_TOKEN_LIFETIME_MINUTES', 30),

];
