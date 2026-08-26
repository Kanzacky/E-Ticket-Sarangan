<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'phone' => $request->validated('phone'),
            'role' => 'wisatawan', // default role
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success('Registrasi berhasil', [
            'user' => $user,
            'access_token' => $token,
        ], [], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();
        $passwordVerified = $user ? Hash::check($credentials['password'], $user->password) : false;

        // TEMPORARY diagnostic log — jangan pernah menampilkan password/hash.
        Log::info('[Auth] login attempt', [
            'email' => $credentials['email'],
            'user_found' => $user !== null,
            'password_verified' => $passwordVerified,
        ]);

        if (! $user || ! $passwordVerified) {
            return ApiResponse::error('Kredensial tidak valid', 401);
        }

        // Revoke all previous tokens for security (optional, but good practice)
        // $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success('Login berhasil', [
            'user' => $user,
            'access_token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success('Logout berhasil');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success('Data user berhasil diambil', [
            'user' => $request->user(),
        ]);
    }
}
