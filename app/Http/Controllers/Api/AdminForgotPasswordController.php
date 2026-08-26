<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class AdminForgotPasswordController extends Controller
{
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        Password::broker('admins')->sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'If that email address exists, a password reset link has been sent.',
        ]);
    }

    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Admin $admin, string $password) {
                $admin->password = $password;
                $admin->save();
                $admin->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], 422);
        }

        return response()->json(['message' => 'Password has been reset successfully.']);
    }
}
