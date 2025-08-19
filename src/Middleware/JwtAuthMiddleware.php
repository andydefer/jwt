<?php

namespace Andydefer\JwtAuth\Middleware;

use Andydefer\JwtAuth\Models\JwtAuth;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth as TymonJWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;

class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token not provided'
                ], 401);
            }

            $jwtAuth = JwtAuth::where('jwt_token', $token)->first();

            if (!$jwtAuth || !$jwtAuth->is_jwt_auth) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired token'
                ], 401);
            }

            // Authenticate user with JWT
            $user = TymonJWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            // Update last used timestamp
            $jwtAuth->update(['last_used_at' => now()]);

            // Attach user and jwtAuth to request
            $request->merge(['jwt_user' => $user]);
            $request->merge(['jwt_auth' => $jwtAuth]);

            return $next($request);
        } catch (JWTException $e) {
            Log::error('JWT Exception: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token'
            ], 401);
        } catch (\Exception $e) {
            Log::error('Auth Middleware Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Authorization error'
            ], 500);
        }
    }
}
