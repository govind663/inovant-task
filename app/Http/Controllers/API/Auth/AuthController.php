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
     * Register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {

                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'status' => true,
                    'message' => 'User registered successfully',
                    'data' => [
                        'token' => $token,
                        'user' => new AuthResource($user),
                    ]
                ], 201);
            });

        } catch (Exception $e) {
            Log::error('Registration Failed', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'Registration failed',
            ], 500);
        }
    }

    /**
     * Login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $user = Auth::user();

            // Revoke old tokens (single device login)
            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => new AuthResource($user),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Login Failed', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'Login failed',
            ], 500);
        }
    }

    /**
     * Logout
     */
    public function logout(): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($user && $user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }

            return response()->json([
                'status' => true,
                'message' => 'Logout successful'
            ]);

        } catch (Exception $e) {
            Log::error('Logout Failed', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'Logout failed'
            ], 500);
        }
    }

    /**
     * Get Auth User
     */
    public function me(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => new AuthResource(auth()->user())
        ]);
    }
}