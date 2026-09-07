<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Avatar Upload Max Size
    |--------------------------------------------------------------------------
    |
    | Maximum allowed size, in kilobytes, for a user avatar. Applies to both
    | multipart file uploads and base64-encoded string uploads.
    |
    */
    'avatar_max_size_kb' => (int) env('AVATAR_MAX_SIZE_KB', 5120),

];
