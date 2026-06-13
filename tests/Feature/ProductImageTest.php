<?php

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('stores product images in a single table', function () {
    expect(Schema::hasTable('product_images'))->toBeTrue()
        ->and(Schema::hasTable('images'))->toBeFalse()
        ->and(Schema::hasColumns('product_images', [
            'id',
            'product_id',
            'url',
            'position',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasColumn('product_images', 'image_id'))->toBeFalse();
});

it('returns product images ordered by position', function () {
    $product = Product::factory()->create();

    ProductImage::factory()->for($product)->create([
        'url' => 'https://example.com/second.jpg',
        'position' => 2,
    ]);
    ProductImage::factory()->for($product)->create([
        'url' => 'https://example.com/main.jpg',
        'position' => 1,
    ]);

    expect($product->images()->pluck('url')->all())->toBe([
        'https://example.com/main.jpg',
        'https://example.com/second.jpg',
    ])->and($product->mainImage())->toBe('https://example.com/main.jpg');
});

it('belongs to a product', function () {
    $product = Product::factory()->create();
    $productImage = ProductImage::factory()->for($product)->create();

    expect($productImage->product->is($product))->toBeTrue();
});
