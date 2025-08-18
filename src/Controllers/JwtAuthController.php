<?php

namespace AndyDefer\Jwt\Controllers;

use App\Models\User;
use AndyDefer\Jwt\Models\JwtAuth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth as TymonJWTAuth;

class JwtAuthController extends Controller
{
    /**
     * Register a new user and generate JWT token
     */
    public function register(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'device_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Generate key pair
            $keyPair = $this->generateKeyPair();
            if (!$keyPair) {
                throw new \Exception('Failed to generate key pair');
            }

            // Generate JWT token
            $token = TymonJWTAuth::fromUser($user);
            $jwtToken = Uuid::uuid4()->toString() . ':' . $token;

            // Create JWT auth record
            $jwtAuth = JwtAuth::create([
                'user_id' => $user->id,
                'jwt_token' => $jwtToken,
                'is_jwt_auth' => true,
                'jwt_issued_at' => now(),
                'public_key' => $keyPair['public_key'],
                'private_key' => $keyPair['private_key'],
                'device_id' => $request->device_id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_used_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => $user,
                    'token' => $jwtToken,
                    'public_key' => $keyPair['public_key'],
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Authenticate user and generate JWT token
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'device_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $credentials = $request->only('email', 'password');

            if (!$token = TymonJWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $user = Auth::user();

            // Generate key pair
            $keyPair = $this->generateKeyPair();
            if (!$keyPair) {
                throw new \Exception('Failed to generate key pair');
            }

            $jwtToken = Uuid::uuid4()->toString() . ':' . $token;

            // Create or update JWT auth record
            $jwtAuth = JwtAuth::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'device_id' => $request->device_id
                ],
                [
                    'jwt_token' => $jwtToken,
                    'is_jwt_auth' => true,
                    'jwt_issued_at' => now(),
                    'public_key' => $keyPair['public_key'],
                    'private_key' => $keyPair['private_key'],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_used_at' => now(),
                ]
            );

            return response()->json([
                'status' => 'success',
                'data' => [
                    'token' => $jwtToken,
                    'public_key' => $keyPair['public_key'],
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Login failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Logout user and invalidate token
     */
    public function logout(Request $request)
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

            if ($jwtAuth) {
                $jwtAuth->update(['is_jwt_auth' => false]);
            }

            TymonJWTAuth::invalidate(TymonJWTAuth::parseToken());

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to logout. Please try again.'
            ], 500);
        }
    }

    /**
     * Get JWT token for authenticated user (via Breeze session)
     */
    public function getToken(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not authenticated'
            ], 401);
        }

        try {
            $user = Auth::user();
            $token = TymonJWTAuth::fromUser($user);
            $jwtToken = Uuid::uuid4()->toString() . ':' . $token;

            // Générer paire de clés
            $keyPair = $this->generateKeyPair();
            if (!$keyPair) {
                throw new \Exception('Failed to generate key pair');
            }

            // Crée l'enregistrement JwtAuth avec des dates Carbon correctes
            $jwtAuth = JwtAuth::create([
                'user_id' => $user->id,
                'jwt_token' => $jwtToken,
                'is_jwt_auth' => true,
                'jwt_issued_at' => Carbon::now(),
                'last_used_at' => Carbon::now(),
                'public_key' => $keyPair['public_key'],
                'private_key' => $keyPair['private_key'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_id' => $request->device_id ?? null,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'token' => $jwtToken,
                    'public_key' => $keyPair['public_key'],
                ]
            ]);
        } catch (\Exception $e) {
            dd($e);
            Log::error('Token generation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate token. Please try again.'
            ], 500);
        }
    }

    /**
     * Refresh JWT token
     */
    public function refresh(Request $request)
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

            // Invalidate old token
            $jwtAuth->update(['is_jwt_auth' => false]);
            TymonJWTAuth::invalidate(TymonJWTAuth::parseToken());

            // Generate new token
            $newToken = TymonJWTAuth::fromUser($jwtAuth->user);
            $newJwtToken = Uuid::uuid4()->toString() . ':' . $newToken;

            // Generate new key pair
            $keyPair = $this->generateKeyPair();
            if (!$keyPair) {
                throw new \Exception('Failed to generate key pair');
            }

            // Create new JWT auth record
            $newJwtAuth = JwtAuth::create([
                'user_id' => $jwtAuth->user_id,
                'jwt_token' => $newJwtToken,
                'is_jwt_auth' => true,
                'jwt_issued_at' => now(),
                'public_key' => $keyPair['public_key'],
                'private_key' => $keyPair['private_key'],
                'device_id' => $jwtAuth->device_id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_used_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'token' => $newJwtToken,
                    'public_key' => $keyPair['public_key'],
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Token refresh error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to refresh token. Please try again.'
            ], 500);
        }
    }

    /**
     * Retourne l'utilisateur authentifié à partir du token JWT
     */
    public function user(Request $request)
    {
        try {
            $user = TymonJWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $user
            ]);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token expired'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token invalid'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token absent'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get user'
            ], 500);
        }
    }

    /**
     * Generate RSA key pair
     */
    private function generateKeyPair()
    {
        try {
            $config = [
                "digest_alg" => "sha512",
                "private_key_bits" => 4096,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            ];

            $keyPair = openssl_pkey_new($config);
            if (!$keyPair) {
                throw new \Exception('Failed to generate key pair');
            }

            // Extract private key
            openssl_pkey_export($keyPair, $privateKey);

            // Extract public key
            $publicKey = openssl_pkey_get_details($keyPair)['key'];

            return [
                'private_key' => $privateKey,
                'public_key' => $publicKey
            ];
        } catch (\Exception $e) {
            Log::error('Key pair generation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify client signature
     */
    public function verifySignature(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'data' => 'required|string',
            'signature' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $jwtAuth = JwtAuth::where('jwt_token', $request->token)->first();

            if (!$jwtAuth || !$jwtAuth->is_jwt_auth) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired token'
                ], 401);
            }

            $publicKey = openssl_pkey_get_public($jwtAuth->public_key);
            $result = openssl_verify(
                $request->data,
                base64_decode($request->signature),
                $publicKey,
                'sha512WithRSAEncryption'
            );

            openssl_free_key($publicKey);

            if ($result === 1) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Signature verified'
                ]);
            } elseif ($result === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid signature'
                ], 401);
            } else {
                throw new \Exception('Error verifying signature');
            }
        } catch (\Exception $e) {
            Log::error('Signature verification error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to verify signature. Please try again.'
            ], 500);
        }
    }
}
