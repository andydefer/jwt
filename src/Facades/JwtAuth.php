<?php

namespace Andydefer\JwtAuth\Facades;

use Illuminate\Support\Facades\Facade;

class JwtAuth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'jwt.auth';
    }
}
