<?php

return [
    'secret' => env('JWT_SECRET'),
    'ttl' => 3600,
    'refresh_ttl' => 20160,
    'algo' => 'HS256',
];
