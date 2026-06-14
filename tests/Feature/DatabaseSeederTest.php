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
        ->and(Schema::hasColumns('reviews', [
            'product_id',
            'user_id',
            'comment',
            'rating',
        ]))->toBeTrue()
        ->and(Schema::hasColumn('products', 'reviews'))->toBeFalse()
        ->and(Schema::hasColumns('order_items', [
            'user_id',
            'order_id',
            'product_id',
            'quantity',
            'unit_price',
            'discount_percentage',
            'total_price',
        ]))->toBeTrue()
        ->and(DB::table('products')->count())->toBeGreaterThan(0)
        ->and(DB::table('reviews')->count())->toBeGreaterThan(0)
        ->and(DB::table('users')->count())->toBe(50)
        ->and(DB::table('reviews')->distinct()->count('user_id'))->toBeLessThanOrEqual(50)
        ->and(DB::table('orders')->count())->toBeGreaterThan(0)
        ->and(DB::table('order_items')->count())->toBeGreaterThan(0)
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
        'reviews' => DB::table('reviews')->count(),
        'orders' => DB::table('orders')->count(),
        'order_items' => DB::table('order_items')->count(),
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
        'reviews' => DB::table('reviews')->count(),
        'orders' => DB::table('orders')->count(),
        'order_items' => DB::table('order_items')->count(),
    ])->toBe($counts);
});
