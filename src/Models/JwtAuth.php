<?php

namespace AndyDefer\Jwt\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class JwtAuth extends Model
{
    use HasFactory;

    protected $table = 'jwt_auth';

    protected $fillable = [
        'user_id',
        'jwt_token',
        'is_jwt_auth',
        'jwt_issued_at',
        'last_used_at',
        'public_key',
        'private_key',
        'device_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'jwt_issued_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_jwt_auth' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Chiffrement automatique de la clé privée
     */
    public function setPrivateKeyAttribute($value)
    {
        $this->attributes['private_key'] = Crypt::encryptString($value);
    }

    /**
     * Déchiffrement automatique de la clé privée
     */
    public function getPrivateKeyAttribute($value)
    {
        return Crypt::decryptString($value);
    }
}
