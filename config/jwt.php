<?php

return [
    'secret' => env('JWT_SECRET'),
    'ttl' => env('JWT_TTL', 60),
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),
    'callbacks' => [
        'register' => null,
        'login' => null,
        'user' => null,
        'resolve_user' => null,
    ],

];
