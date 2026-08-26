<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    #[
        OA\Get(
            path: '/api/profile',
            summary: 'Get authenticated user profile',
            tags: ['Profile'],
            security: [['sanctum' => []]],
            responses: [
                new OA\Response(response: 200, description: 'User profile', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/User'),
                    ]
                )),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()]);
    }

    #[
        OA\Patch(
            path: '/api/profile',
            summary: 'Update name, email, phone, avatar',
            tags: ['Profile'],
            security: [['sanctum' => []]],
            requestBody: new OA\RequestBody(
                required: false,
                content: new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                            new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                            new OA\Property(property: 'phone', type: 'string', example: '+1234567890'),
                            new OA\Property(property: 'avatar', type: 'string', format: 'binary', description: 'Image file (jpg, jpeg, png, webp, max 2MB)'),
                        ]
                    )
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Profile updated', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Profile updated successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/User'),
                    ]
                )),
                new OA\Response(response: 422, description: 'Validation error'),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'   => 'sometimes|required|string|max:255',
            'email'  => 'sometimes|required|email|unique:users,email,' . $user->id,
            'phone'  => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')
                ->store('avatars', 'public');
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data'    => $user->fresh(),
        ]);
    }

    #[
        OA\Patch(
            path: '/api/profile/password',
            summary: 'Change the authenticated user password',
            tags: ['Profile'],
            security: [['sanctum' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['current_password', 'password', 'password_confirmation'],
                    properties: [
                        new OA\Property(property: 'current_password', type: 'string', format: 'password', example: 'oldpassword123'),
                        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'newpassword123'),
                        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'newpassword123'),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Password changed', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
                new OA\Response(response: 422, description: 'Current password incorrect or validation error'),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Password changed successfully.']);
    }

    /**
     * Upload the user avatar (POST so multipart parsing works reliably).
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = $request->user();

        if ($user->avatar) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
        }

        $user->update([
            'avatar' => $validated['avatar']->store('avatars', 'public'),
        ]);

        return response()->json([
            'message' => 'Avatar updated successfully.',
            'data'    => ['avatar_url' => asset('storage/' . $user->avatar)],
        ]);
    }
}
