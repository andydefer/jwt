<?php

namespace Andydefer\JwtAuth\Controllers;

use Illuminate\Http\Request;
use Andydefer\JwtAuth\JwtAuth as JwtAuthService;
use Andydefer\JwtAuth\Models\AutoUser;
use Andydefer\JwtAuth\Services\JwtCallbackService;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtAuthController
{
    protected $jwtAuth;

    public function __construct(JwtAuthService $jwtAuth)
    {
        $this->jwtAuth = $jwtAuth;
    }

    /**
     * Enregistre un nouvel utilisateur (customisable)
     */
    public function register(Request $request)
    {
        $user = JwtCallbackService::register($request);
        $token = $this->jwtAuth->createTokenForUser($user, $request);

        return response()->json([
            'user' => JwtCallbackService::transformUser($user),
            'token' => $token
        ], 201);
    }

    /**
     * Connexion (customisable)
     */
    public function login(Request $request)
    {
        $userClass = JwtCallbackService::resolveUserModel();
        $user = $userClass::where('email', $request->email)->first();

        if (!JwtCallbackService::login($request, $user)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = $this->jwtAuth->createTokenForUser($user, $request);

        return response()->json([
            'user' => JwtCallbackService::transformUser($user),
            'token' => $token
        ]);
    }

    /**
     * Utilisateur authentifié (customisable)
     */
    public function user()
    {
        $user = $this->jwtAuth->getAuthenticatedUser();

        return response()->json([
            'user' => JwtCallbackService::transformUser($user)
        ]);
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        $token = JWTAuth::getToken();
        $this->jwtAuth->invalidateToken($token);

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Rafraîchir le token
     */
    public function refresh()
    {
        $token = JWTAuth::getToken();
        $newToken = $this->jwtAuth->refreshToken($token);

        return response()->json(['token' => $newToken]);
    }

    /**
     * Retourner le token actuel
     */
    public function token()
    {
        $token = JWTAuth::getToken();
        return response()->json(['token' => $token]);
    }
}
