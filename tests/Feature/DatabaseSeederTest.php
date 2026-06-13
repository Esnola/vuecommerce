<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('builds and seeds the final schema without corrective migrations', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Schema::hasTable('images'))->toBeFalse()
        ->and(Schema::hasColumns('product_images', [
            'product_id',
            'url',
            'position',
        ]))->toBeTrue()
        ->and(Schema::hasColumn('product_images', 'image_id'))->toBeFalse()
        ->and(Schema::hasColumns('cart_items', [
            'user_id',
            'product_id',
            'quantity',
        ]))->toBeTrue()
        ->and(Schema::hasColumn('cart_items', 'order_id'))->toBeFalse()
        ->and(DB::table('products')->count())->toBeGreaterThan(0)
        ->and(DB::table('product_images')->count())->toBeGreaterThan(0)
        ->and(DB::table('tags')->count())->toBeGreaterThan(0);
});

it('can run the complete database seeder repeatedly', function () {
    $this->seed(DatabaseSeeder::class);

    $counts = [
        'users' => DB::table('users')->count(),
        'countries' => DB::table('countries')->count(),
        'products' => DB::table('products')->count(),
        'categories' => DB::table('categories')->count(),
        'tags' => DB::table('tags')->count(),
        'product_categories' => DB::table('product_categories')->count(),
        'product_tags' => DB::table('product_tags')->count(),
        'product_images' => DB::table('product_images')->count(),
    ];

    $this->seed(DatabaseSeeder::class);

    expect([
        'users' => DB::table('users')->count(),
        'countries' => DB::table('countries')->count(),
        'products' => DB::table('products')->count(),
        'categories' => DB::table('categories')->count(),
        'tags' => DB::table('tags')->count(),
        'product_categories' => DB::table('product_categories')->count(),
        'product_tags' => DB::table('product_tags')->count(),
        'product_images' => DB::table('product_images')->count(),
    ])->toBe($counts);
});
