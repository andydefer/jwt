<?php

namespace Andydefer\JwtAuth\Models;

use Illuminate\Database\Eloquent\Model;

class JwtAuth extends Model
{
    protected $table = 'jwt_auth';

    protected $fillable = [
        'user_id',
        'jwt_token',
        'device_id',
        'ip_address',
        'user_agent',
        'is_jwt_auth',
        'jwt_issued_at',
        'last_used_at',
    ];

    protected $casts = [
        'is_jwt_auth' => 'boolean',
        'jwt_issued_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];
}
