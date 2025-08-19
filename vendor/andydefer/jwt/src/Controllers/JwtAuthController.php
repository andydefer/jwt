<?php

namespace Andydefer\JwtAuth\Controllers;

use Illuminate\Http\Request;
use Andydefer\JwtAuth\JwtAuth as JwtAuthService;
use AndyDefer\JwtAuth\Models\AutoUser;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class JwtAuthController
{
    protected $jwtAuth;

    public function __construct(JwtAuthService $jwtAuth)
    {
        $this->jwtAuth = $jwtAuth;
    }

    /**
     * Résout le modèle utilisateur à utiliser
     */
    protected function resolveUserModel(): string
    {
        $userClass = config('auth.providers.users.model');

        if (!in_array(\Tymon\JWTAuth\Contracts\JWTSubject::class, class_implements($userClass))) {
            return AutoUser::class;
        }

        return $userClass;
    }

    /**
     * Enregistre un nouvel utilisateur
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $userClass = $this->resolveUserModel();

        $user = $userClass::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $this->jwtAuth->createTokenForUser($user, $request);

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Connexion
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $userClass = $this->resolveUserModel();
        $user = $userClass::where('email', $credentials['email'])->first();

        if (!$user || !password_verify($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = $this->jwtAuth->createTokenForUser($user, $request);

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Utilisateur authentifié
     */
    public function user()
    {
        $user = $this->jwtAuth->getAuthenticatedUser();
        return response()->json(['user' => $user]);
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
