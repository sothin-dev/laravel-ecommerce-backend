<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'phone' => '0987654321',
                'password' => Hash::make('testuser123'),
            ]
        );

        $this->call([
            AdminSeeder::class,
            TestDataSeeder::class,
            CouponSeeder::class,
            ProductImageSeeder::class,
        ]);

        $this->command->info('────────────────────────────────');
        $this->command->info(' All seeder completed!');
        $this->command->info(' Admin: admin@example.com / admin12345');
        $this->command->info(' User:  customer1@example.com / password');
        $this->command->info('────────────────────────────────');
    }
}
