<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code'             => 'WELCOME10',
                'type'             => 'percentage',
                'value'            => 10,
                'min_order_amount' => 0,
                'max_discount'     => 50,
                'usage_limit'      => 1000,
                'is_active'        => true,
                'description'      => '10% off your first order (max $50).',
            ],
            [
                'code'             => 'SAVE20',
                'type'             => 'fixed',
                'value'            => 20,
                'min_order_amount' => 100,
                'max_discount'     => null,
                'usage_limit'      => 500,
                'is_active'        => true,
                'description'      => '$20 off orders over $100.',
            ],
            [
                'code'             => 'FREESHIP',
                'type'             => 'fixed',
                'value'            => 5,
                'min_order_amount' => 0,
                'max_discount'     => null,
                'usage_limit'      => null,
                'is_active'        => true,
                'description'      => '$5 off any order.',
            ],
            [
                'code'             => 'EXPIRED',
                'type'             => 'percentage',
                'value'            => 30,
                'min_order_amount' => 0,
                'max_discount'     => null,
                'usage_limit'      => null,
                'is_active'        => true,
                'expires_at'       => now()->subDays(1),
                'description'      => 'An already-expired promo (for testing).',
            ],
        ];

        foreach ($coupons as $data) {
            Coupon::firstOrCreate(['code' => $data['code']], $data);
        }

        $this->command->info('✓ Coupons seeded.');
    }
}
