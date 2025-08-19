<?php

namespace Andydefer\JwtAuth;

use Andydefer\JwtAuth\Models\JwtAuth as JwtToken;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;

class JwtAuth
{
    public function createTokenForUser($user, $request)
    {
        $token = FacadesJWTAuth::fromUser($user);

        JwtToken::create([
            'user_id' => $user->id,
            'jwt_token' => $token,
            'device_id' => $request->device_id ?? uniqid(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_jwt_auth' => true,
            'jwt_issued_at' => now(),
            'last_used_at' => now(),
        ]);

        return $token;
    }

    public function invalidateToken($token)
    {
        JwtToken::where('jwt_token', $token)->delete();
        FacadesJWTAuth::invalidate($token);
    }

    public function refreshToken($token)
    {
        $newToken = FacadesJWTAuth::refresh($token);

        $jwtToken = JwtToken::where('jwt_token', $token)->first();
        if ($jwtToken) {
            $jwtToken->update([
                'jwt_token' => $newToken,
                'last_used_at' => now(),
            ]);
        }

        return $newToken;
    }

    public function getAuthenticatedUser()
    {
        return Auth::user();
    }
}
