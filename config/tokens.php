<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Account Activation Tokens
    |--------------------------------------------------------------------------
    |
    | These settings control the generation and validation of activation
    | tokens used when inviting or provisioning new users. The secret is
    | used to sign the activation JWT, while the TTL determines how long
    | the activation link remains valid.
    |
    */

    'activation' => [
        'secret' => env('ACTIVATION_TOKEN_SECRET'),
        'ttl_hours' => env('ACTIVATION_TOKEN_TTL_HOURS', 2),
    ],

];