<?php

namespace Database\Seeders;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'first_name' => 'JuanJota',
                'last_name' => 'JotaJuan',
                'email' => 'test@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $admin->forceFill([
            'is_admin' => true,
            'status' => UserStatusEnum::ACTIVE,
        ])->save();

        $this->call([
            CountrySeeder::class,
            ProductSeeder::class,
            ReviewSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            ProductRelationsSeeder::class,
            ProductImageSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
        ]);
    }
}
