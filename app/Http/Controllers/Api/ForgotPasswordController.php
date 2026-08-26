<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        // Always respond the same way to avoid account enumeration
        Password::broker('users')->sendResetLink($request->only('email'));

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

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => \Illuminate\Support\Facades\Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Revoke all existing API tokens for security
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'message' => 'Password has been reset successfully. You can now log in with your new password.',
        ]);
    }
}
