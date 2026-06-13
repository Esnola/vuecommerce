<?php

namespace Database\Seeders;

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
                'name' => 'JuanJota',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $admin->forceFill(['is_admin' => true])->save();

        $this->call([
            CountrySeeder::class,
            ProductSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            ProductRelationsSeeder::class,
            ProductImageSeeder::class,
        ]);
    }
}
