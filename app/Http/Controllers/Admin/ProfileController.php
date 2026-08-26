<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var \App\Models\Admin $admin */
        $admin = $request->user('admin');

        return response()->json([
            'data' => ['id' => $admin->id, 'name' => $admin->name, 'email' => $admin->email],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        /** @var \App\Models\Admin $admin */
        $admin = $request->user('admin');

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admins,email,' . $admin->id,
        ]);

        $admin->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data'    => ['id' => $admin->id, 'name' => $admin->name, 'email' => $admin->email],
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        /** @var \App\Models\Admin $admin */
        $admin = $request->user('admin');

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($validated['current_password'], $admin->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors'  => ['current_password' => ['The current password is incorrect.']],
            ], 422);
        }

        $admin->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['message' => 'Password changed successfully.']);
    }
}
