<?php

return [
    // Clé secrète par défaut
    'secret' => env('JWT_SECRET', 'change_this_secret'),

    // Durée de vie du token (en minutes)
    'ttl' => env('JWT_TTL', 60),
];
