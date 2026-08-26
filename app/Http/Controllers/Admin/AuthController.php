<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        /** @var Admin|null $admin */
        $admin = Admin::where('email', $credentials['email'])->first();

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            return response()->json([
                'message' => 'Invalid email or password.',
                'errors'  => ['email' => ['Invalid email or password.']],
            ], 401);
        }

        $admin->tokens()->delete();
        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'admin'   => ['id' => $admin->id, 'name' => $admin->name, 'email' => $admin->email],
            'token'   => $token,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user('admin');

        return response()->json([
            'data' => ['id' => $admin->id, 'name' => $admin->name, 'email' => $admin->email],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user('admin')->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
