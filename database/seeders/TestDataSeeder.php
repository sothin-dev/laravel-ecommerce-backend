<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Users (15 for pagination testing) ───
        $users = [];
        for ($i = 1; $i <= 15; $i++) {
            $users[] = User::firstOrCreate(
                ['email' => "customer{$i}@example.com"],
                [
                    'name'     => "Customer {$i}",
                    'phone'    => '012' . str_pad((string) rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                    'password' => Hash::make('password'),
                ]
            );
        }

        // ─── Categories with hierarchy (12 total for pagination) ───
        $parentCategories = [];
        $categories = [];

        // 6 parent categories
        $parentNames = [
            ['name' => 'Electronics',     'slug' => 'electronics'],
            ['name' => 'Clothing',        'slug' => 'clothing'],
            ['name' => 'Home & Garden',   'slug' => 'home-garden'],
            ['name' => 'Sports & Outdoors','slug' => 'sports-outdoors'],
            ['name' => 'Books & Media',   'slug' => 'books-media'],
            ['name' => 'Toys & Games',    'slug' => 'toys-games'],
        ];

        foreach ($parentNames as $p) {
            $cat = Category::firstOrCreate(
                ['slug' => $p['slug']],
                [
                    'name'        => $p['name'],
                    'description' => "All {$p['name']} products and accessories.",
                    'is_active'   => true,
                    'parent_id'   => null,
                ]
            );
            $parentCategories[] = $cat;
            $categories[] = $cat;
        }

        // 6 child categories (one per parent)
        $childData = [
            ['parent' => 'Electronics',      'name' => 'Smartphones',  'slug' => 'smartphones'],
            ['parent' => 'Clothing',         'name' => 'T-Shirts',     'slug' => 't-shirts'],
            ['parent' => 'Home & Garden',    'name' => 'Furniture',    'slug' => 'furniture'],
            ['parent' => 'Sports & Outdoors', 'name' => 'Football',    'slug' => 'football'],
            ['parent' => 'Books & Media',    'name' => 'Fiction',      'slug' => 'fiction'],
            ['parent' => 'Toys & Games',     'name' => 'Board Games',  'slug' => 'board-games'],
        ];

        foreach ($childData as $c) {
            $parent = collect($parentCategories)->firstWhere('name', $c['parent']);
            $cat = Category::firstOrCreate(
                ['slug' => $c['slug']],
                [
                    'name'        => $c['name'],
                    'description' => "{$c['name']} subcategory.",
                    'is_active'   => true,
                    'parent_id'   => $parent?->id,
                ]
            );
            $categories[] = $cat;
        }

        $categoryIds = collect($categories)->pluck('id')->toArray();

        // ─── Products (25 for pagination — 3 admin pages, 2 frontend pages) ───
        $products = [];
        $productNames = [
            ['name' => 'Wireless Headphones',     'sku' => 'WH-001', 'price' => 79.99,  'sale_price' => 59.99, 'image' => null],
            ['name' => 'Bluetooth Speaker',       'sku' => 'BS-002', 'price' => 49.99,  'sale_price' => null,   'image' => null],
            ['name' => 'USB-C Hub 7-in-1',        'sku' => 'UC-003', 'price' => 34.99,  'sale_price' => 24.99, 'image' => null],
            ['name' => 'Mechanical Keyboard',     'sku' => 'MK-004', 'price' => 119.99, 'sale_price' => null,   'image' => null],
            ['name' => 'Cotton T-Shirt',          'sku' => 'CT-005', 'price' => 24.99,  'sale_price' => 19.99, 'image' => null],
            ['name' => 'Denim Jacket',            'sku' => 'DJ-006', 'price' => 89.99,  'sale_price' => null,   'image' => null],
            ['name' => 'Running Shoes',           'sku' => 'RS-007', 'price' => 129.99, 'sale_price' => 99.99, 'image' => null],
            ['name' => 'Yoga Mat',                'sku' => 'YM-008', 'price' => 29.99,  'sale_price' => null,   'image' => null],
            ['name' => 'Indoor Plant Pot',        'sku' => 'IP-009', 'price' => 39.99,  'sale_price' => null,   'image' => null],
            ['name' => 'LED Desk Lamp',           'sku' => 'LL-010', 'price' => 44.99,  'sale_price' => 34.99, 'image' => null],
            ['name' => 'Leather Wallet',          'sku' => 'LW-011', 'price' => 54.99,  'sale_price' => null,   'image' => null],
            ['name' => 'Backpack 40L',            'sku' => 'BP-012', 'price' => 69.99,  'sale_price' => 49.99, 'image' => null],
            ['name' => 'Stainless Water Bottle',  'sku' => 'WB-013', 'price' => 19.99,  'sale_price' => null,   'image' => null],
            ['name' => 'Sunglasses Aviator',      'sku' => 'SG-014', 'price' => 149.99, 'sale_price' => 99.99, 'image' => null],
            ['name' => 'Wireless Mouse',          'sku' => 'WM-015', 'price' => 39.99,  'sale_price' => 29.99, 'image' => null],
            ['name' => 'HDMI Cable 6ft',          'sku' => 'HC-016', 'price' => 12.99,  'sale_price' => null,   'image' => null],
            ['name' => 'Power Bank 20000mAh',     'sku' => 'PB-017', 'price' => 45.99,  'sale_price' => null,   'image' => null],
            ['name' => 'Notebook Set',            'sku' => 'NB-018', 'price' => 14.99,  'sale_price' => 9.99,   'image' => null],
            ['name' => 'Desk Organizer',          'sku' => 'DO-019', 'price' => 22.99,  'sale_price' => null,   'image' => null],
            ['name' => 'Wall Clock Modern',       'sku' => 'WC-020', 'price' => 32.99,  'sale_price' => null,   'image' => null],
            ['name' => 'Coffee Mug 400ml',        'sku' => 'CM-021', 'price' => 15.99,  'sale_price' => 11.99, 'image' => null],
            ['name' => 'Smart LED Bulb',          'sku' => 'LB-022', 'price' => 18.99,  'sale_price' => null,   'image' => null],
            ['name' => 'Fitness Tracker',         'sku' => 'FT-023', 'price' => 89.99,  'sale_price' => 69.99, 'image' => null],
            ['name' => 'Portable Charger',        'sku' => 'PC-024', 'price' => 29.99,  'sale_price' => null,   'image' => null],
            ['name' => 'Canvas Sneakers',         'sku' => 'CS-025', 'price' => 59.99,  'sale_price' => 44.99, 'image' => null],
        ];

        foreach ($productNames as $i => $p) {
            $product = Product::firstOrCreate(
                ['sku' => $p['sku']],
                [
                    'category_id' => $categoryIds[array_rand($categoryIds)],
                    'name'        => $p['name'],
                    'slug'        => $p['sku'],
                    'description' => "High-quality {$p['name']} — perfect for everyday use. Durable, stylish, and built to last.",
                    'price'       => $p['price'],
                    'sale_price'  => $p['sale_price'],
                    'image'       => $p['image'],
                    'stock'       => rand(5, 100),
                    'is_active'   => true,
                ]
            );
            $products[] = $product;
        }

        // ─── Orders (15 for pagination — 2 admin pages) ───
        $statuses       = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $paymentMethods = ['credit_card', 'paypal', 'bank_transfer', 'cod'];
        $paymentStatuses = ['pending', 'paid', 'refunded'];

        for ($i = 1; $i <= 15; $i++) {
            $numItems     = rand(1, 4);
            $subtotal     = 0;
            $orderItems   = [];
            $usedProducts = [];

            for ($j = 0; $j < $numItems; $j++) {
                // Pick a product not already in this order
                $available = array_filter($products, fn ($p) => !in_array($p->id, $usedProducts));
                if (empty($available)) break;
                $product    = $available[array_rand($available)];
                $usedProducts[] = $product->id;

                $qty         = rand(1, 3);
                $unitPrice   = $product->sale_price ?? $product->price;
                $itemSubtotal = $unitPrice * $qty;
                $subtotal    += $itemSubtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal'   => $itemSubtotal,
                ];
            }

            $shippingFee = rand(0, 1) ? 9.99 : 0;
            $orderNumber = 'ORD-' . str_pad((string) (1000 + $i), 6, '0', STR_PAD_LEFT);

            $order = Order::firstOrCreate(
                ['order_number' => $orderNumber],
                [
                'user_id'          => $users[array_rand($users)]->id,
                'order_number'     => $orderNumber,
                'status'           => $statuses[array_rand($statuses)],
                'subtotal'         => $subtotal,
                'shipping_fee'     => $shippingFee,
                'total'            => $subtotal + $shippingFee,
                'shipping_address' => "{$i} Main Street, Phnom Penh, Cambodia",
                'payment_method'   => $paymentMethods[array_rand($paymentMethods)],
                'payment_status'   => $paymentStatuses[array_rand($paymentStatuses)],
                'created_at'       => now()->subDays(rand(0, 60)),
                ]
            );

            foreach ($orderItems as $item) {
                $order->items()->firstOrCreate(
                    ['product_id' => $item['product_id']],
                    $item
                );
            }
        }

        // ─── Reviews (a few on random products) ───
        $ratings = [3, 4, 5];
        $comments = [
            'Great product, highly recommend!',
            'Good quality for the price.',
            'Exactly as described. Very happy.',
            'Decent product, fast shipping.',
            'Amazing quality! Will buy again.',
            'Solid build and works perfectly.',
        ];

        foreach ($products as $i => $product) {
            if ($i % 3 === 0) continue; // skip some

            $shuffledUsers = $users;
            shuffle($shuffledUsers);
            $numReviews = min(rand(1, 3), count($shuffledUsers));

            for ($r = 0; $r < $numReviews; $r++) {
                Review::firstOrCreate(
                    [
                        'user_id'    => $shuffledUsers[$r]->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'order_id'    => null,
                        'rating'      => $ratings[array_rand($ratings)],
                        'comment'     => $comments[array_rand($comments)],
                        'is_approved' => (bool) rand(0, 1),
                    ]
                );
            }
        }

        $this->command->info('✓ Test data seeded successfully!');
        $this->command->info("  - Users:     " . count($users));
        $this->command->info("  - Categories: " . count($categories));
        $this->command->info("  - Products:  " . count($products));
        $this->command->info("  - Orders:    15 (with items)");
        $this->command->info("  - Reviews:   seeded across products");
    }
}
