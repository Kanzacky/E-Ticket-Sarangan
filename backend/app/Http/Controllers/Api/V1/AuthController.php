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
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return ApiResponse::error('Kredensial tidak valid', 401);
        }

        // Revoke all previous tokens for security (optional, but good practice)
        $user->tokens()->delete();

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
