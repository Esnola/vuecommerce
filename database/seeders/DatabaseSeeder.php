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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'JuanJota',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);
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
