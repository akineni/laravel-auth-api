<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Frontend Application URLs
    |--------------------------------------------------------------------------
    |
    | These URLs are used when generating links that redirect the user back
    | to the frontend application (for example password reset links).
    |
    */

    'reset_password_url' => env(
        'FRONTEND_RESET_PASSWORD_URL',
        'http://localhost:3000/reset-password'
    ),

    'email_verification_url' => env(
        'FRONTEND_EMAIL_VERIFICATION_URL',
        'http://localhost:3000/verify-email'
    ),

    /*
    |--------------------------------------------------------------------------
    | Allowed Frontend Hosts
    |--------------------------------------------------------------------------
    |
    | If you ever allow callback URLs from the client, these hosts will be
    | validated against this allowlist to prevent malicious redirects.
    |
    */

    'allowed_hosts' => [
        'localhost',
        '127.0.0.1',
        'app.example.com',
        'staging.example.com',
    ],

];