<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\AuthResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AuthController extends Controller
{
    /**
     * Register (User + Admin दोनों support)
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {

                // 🔥 Default role = user
                $role = $request->input('role', User::ROLE_USER);

                // 🔥 Only allow admin if explicitly passed (optional security)
                if ($role === User::ROLE_ADMIN) {
                    throw new Exception('Admin registration not allowed', 403);
                }

                $user = User::create([
                    'name' => $request->name,
                    'email' => strtolower($request->email),
                    'password' => Hash::make($request->password),

                    // ✅ BOTH handled
                    'role' => $role,
                    'is_admin' => $role === User::ROLE_ADMIN
                ]);

                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'status' => true,
                    'message' => ucfirst($role) . ' registered successfully',
                    'data' => [
                        'token' => $token,
                        'user' => new AuthResource($user),
                    ]
                ], 201);
            });

        } catch (Exception $e) {
            Log::error('Registration Failed', [
                'email' => $request->email ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Registration failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = [
                'email' => strtolower($request->email),
                'password' => $request->password
            ];

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $user = Auth::user();

            // 🔥 Single device login
            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => new AuthResource($user),
                    'is_admin' => $user->isAdmin(), // 🔥 extra clarity
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Login Failed', [
                'email' => $request->email ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Login failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Logout
     */
    public function logout(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $user->currentAccessToken()?->delete();

            return response()->json([
                'status' => true,
                'message' => 'Logout successful'
            ]);

        } catch (Exception $e) {
            Log::error('Logout Failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Logout failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get Auth User
     */
    public function me(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            return response()->json([
                'status' => true,
                'data' => new AuthResource($user),
                'is_admin' => $user->isAdmin()
            ]);

        } catch (Exception $e) {
            Log::error('Fetch Auth User Failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch user',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}