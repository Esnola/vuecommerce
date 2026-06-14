<?php

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('shows the comments related to a product', function () {
    $product = Product::factory()->create();
    $ana = User::factory()->create([
        'first_name' => 'Ana',
        'last_name' => 'Garcia',
        'email' => 'ana@example.com',
    ]);
    $luis = User::factory()->create([
        'first_name' => 'Luis',
        'last_name' => 'Martin',
        'email' => 'luis@example.com',
    ]);

    Review::factory()->for($product)->for($ana)->create([
        'rating' => 5,
        'comment' => 'Excellent quality and fast delivery.',
        'created_at' => '2026-06-10 09:41:02',
    ]);
    Review::factory()->for($product)->for($luis)->create([
        'rating' => 4,
        'comment' => 'The product matches its description.',
        'created_at' => '2026-06-11 09:41:02',
    ]);

    $this->get(route('products.show', $product->slug))
        ->assertSuccessful()
        ->assertSee('Comments')
        ->assertSee('2 customer reviews')
        ->assertSee('Excellent quality and fast delivery.')
        ->assertSee('Ana Garcia')
        ->assertSee('The product matches its description.')
        ->assertSee('Luis Martin');
});

it('shows an empty state when a product has no comments', function () {
    $product = Product::factory()->create();

    $this->get(route('products.show', $product->slug))
        ->assertSuccessful()
        ->assertSee('0 customer reviews')
        ->assertSee('This product has no comments yet.');
});

it('stores reviews with their buyer and product relationships', function () {
    $review = Review::factory()->create([
        'rating' => 5,
    ]);

    expect(Schema::hasColumns('reviews', [
        'product_id',
        'user_id',
        'comment',
        'rating',
    ]))->toBeTrue()
        ->and(Schema::hasColumn('products', 'reviews'))->toBeFalse()
        ->and($review->product)->toBeInstanceOf(Product::class)
        ->and($review->user)->toBeInstanceOf(User::class)
        ->and($review->rating)->toBe(5);
});

it('rejects ratings outside the zero to five range', function (int $rating) {
    expect(fn () => Review::factory()->create([
        'rating' => $rating,
    ]))->toThrow(QueryException::class);
})->with([-1, 6]);
