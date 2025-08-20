<?php

namespace Andydefer\JwtAuth\Services;

use Illuminate\Http\Request;
use Andydefer\JwtAuth\Models\AutoUser;
use Illuminate\Support\Facades\Hash;

class JwtCallbackService
{
    /**
     * Résout le modèle User à utiliser.
     */
    public static function resolveUserModel(): string
    {
        // Callback custom
        if (is_callable(config('jwt.callbacks.resolve_user'))) {
            return call_user_func(config('jwt.callbacks.resolve_user'));
        }

        // Modèle par défaut depuis la config auth
        $userClass = config('auth.providers.users.model');

        if (!in_array(\Tymon\JWTAuth\Contracts\JWTSubject::class, class_implements($userClass))) {
            return AutoUser::class;
        }

        return $userClass;
    }

    /**
     * Execute la logique d'enregistrement (customisable).
     */
    public static function register(Request $request)
    {
        if (is_callable(config('jwt.callbacks.register'))) {
            return call_user_func(config('jwt.callbacks.register'), $request);
        }

        // Comportement par défaut
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $userClass = self::resolveUserModel();

        return $userClass::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
    }

    /**
     * Execute la logique de login (customisable).
     */
    public static function login(Request $request, $user): bool
    {
        if (is_callable(config('jwt.callbacks.login'))) {
            return call_user_func(config('jwt.callbacks.login'), $request, $user);
        }

        // Comportement par défaut
        return $user && password_verify($request->password, $user->password);
    }

    /**
     * Transforme l'utilisateur avant la réponse (customisable).
     */
    public static function transformUser($user)
    {
        if (is_callable(config('jwt.callbacks.user'))) {
            return call_user_func(config('jwt.callbacks.user'), $user);
        }

        return $user;
    }
}
