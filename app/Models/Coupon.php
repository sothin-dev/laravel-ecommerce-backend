<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'type',
    'value',
    'min_order_amount',
    'max_discount',
    'usage_limit',
    'used_count',
    'starts_at',
    'expires_at',
    'is_active',
    'description',
])]
#[Hidden(['used_count'])]
class Coupon extends Model
{
    protected function casts(): array
    {
        return [
            'value'            => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount'     => 'decimal:2',
            'usage_limit'      => 'integer',
            'used_count'       => 'integer',
            'is_active'        => 'boolean',
            'starts_at'        => 'datetime',
            'expires_at'       => 'datetime',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Whether the coupon is currently active and within its time window.
     */
    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->greaterThan($now)) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->lessThan($now)) {
            return false;
        }

        return true;
    }

    /**
     * Compute the discount amount for a given subtotal (cannot exceed subtotal).
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percentage') {
            $discount = $subtotal * ($this->value / 100);

            if ($this->max_discount !== null) {
                $discount = min($discount, (float) $this->max_discount);
            }
        } else {
            $discount = (float) $this->value;
        }

        return round(min($discount, $subtotal), 2);
    }

    /**
     * Validate the coupon against a subtotal and (optionally) a user.
     * Returns an array with 'valid' (bool) and 'message' / 'discount'.
     */
    public function evaluate(float $subtotal, ?User $user = null): array
    {
        if (! $this->isCurrentlyActive()) {
            return ['valid' => false, 'message' => 'This coupon is not active.'];
        }

        if ((float) $this->min_order_amount > $subtotal) {
            return [
                'valid'   => false,
                'message' => 'Minimum order amount of $' . number_format($this->min_order_amount, 2) . ' required.',
            ];
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'This coupon has reached its usage limit.'];
        }

        if ($user && $this->usages()->where('user_id', $user->id)->exists()) {
            return ['valid' => false, 'message' => 'You have already used this coupon.'];
        }

        return [
            'valid'    => true,
            'message'  => 'Coupon applied.',
            'discount' => $this->calculateDiscount($subtotal),
        ];
    }
}
