<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CouponController extends Controller
{
    #[
        OA\Post(
            path: '/api/coupon/validate',
            summary: 'Validate a coupon code and compute its discount',
            tags: ['Coupons'],
            security: [['sanctum' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['code', 'subtotal'],
                    properties: [
                        new OA\Property(property: 'code', type: 'string', example: 'WELCOME10'),
                        new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 120),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Validation result'),
                new OA\Response(response: 422, description: 'Validation error'),
            ]
        )
    ]
    public function validate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'     => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper(trim($validated['code'])))->first();

        if (! $coupon) {
            return response()->json([
                'valid'   => false,
                'message' => 'Invalid coupon code.',
            ], 422);
        }

        $evaluation = $coupon->evaluate((float) $validated['subtotal'], $request->user());

        return response()->json([
            'valid'    => $evaluation['valid'],
            'message'  => $evaluation['message'],
            'discount' => $evaluation['discount'] ?? 0,
            'code'     => $coupon->code,
            'type'     => $coupon->type,
            'value'    => (float) $coupon->value,
        ], $evaluation['valid'] ? 200 : 422);
    }
}
