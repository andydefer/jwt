<?php

namespace AndyDefer\Jwt\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth as TymonJWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use AndyDefer\Jwt\Models\JwtAuth;
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

            // Extract the UUID:JWT parts
            $parts = explode(':', $token, 2);
            if (count($parts) !== 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid token format'
                ], 401);
            }

            $jwtAuth = JwtAuth::where('jwt_token', $token)->first();

            if (!$jwtAuth || !$jwtAuth->is_jwt_auth) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired token'
                ], 401);
            }

            // Verify token signature using JWT-Auth facade alias
            $user = TymonJWTAuth::setToken($parts[1])->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            // Verify client signature if required
            if ($request->hasHeader('X-Signature') && $request->hasHeader('X-Signed-Data')) {
                $publicKey = openssl_pkey_get_public($jwtAuth->public_key);
                $result = openssl_verify(
                    $request->header('X-Signed-Data'),
                    base64_decode($request->header('X-Signature')),
                    $publicKey,
                    'sha512WithRSAEncryption'
                );

                openssl_free_key($publicKey);

                if ($result !== 1) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid request signature'
                    ], 401);
                }
            }

            // Update last used at
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
