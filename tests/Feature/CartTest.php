<?php

use App\Enums\ProductStatusEnum;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('stores unique cart items per user and product', function () {
    expect(Schema::hasTable('cart_items'))->toBeTrue()
        ->and(Schema::hasColumns('cart_items', [
            'user_id',
            'product_id',
            'quantity',
            'created_at',
            'updated_at',
        ]))->toBeTrue();

    $user = User::factory()->create();
    $product = Product::factory()->create();

    $user->cartItems()->create([
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    expect($user->cartItems()->first()->product->is($product))->toBeTrue()
        ->and($product->cartItems()->first()->user->is($user))->toBeTrue();
});

it('adds a product to an authenticated users cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'stock' => 5,
        'availability_status' => ProductStatusEnum::IN_STOCK,
    ]);

    $this->actingAs($user)
        ->post(route('cart.store', $product), ['quantity' => 2])
        ->assertRedirect();

    expect($user->cartItems()->whereBelongsTo($product)->first())
        ->quantity->toBe(2);
});

it('returns cart totals when adding a product with json', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'stock' => 5,
        'availability_status' => ProductStatusEnum::IN_STOCK,
    ]);

    $this->actingAs($user)
        ->postJson(route('cart.store', $product), ['quantity' => 2])
        ->assertSuccessful()
        ->assertJson([
            'cart_count' => 2,
            'item_quantity' => 2,
            'message' => 'Product added to cart.',
        ]);
});

it('uses one as the default quantity regardless of the products minimum order quantity', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'stock' => 8,
        'minimum_order_quantity' => 3,
        'availability_status' => ProductStatusEnum::IN_STOCK,
    ]);

    $this->actingAs($user)
        ->postJson(route('cart.store', $product))
        ->assertSuccessful()
        ->assertJson([
            'cart_count' => 1,
            'item_quantity' => 1,
        ]);

    expect($user->cartItems()->whereBelongsTo($product)->first())
        ->quantity->toBe(1);
});

it('increments an existing cart item without exceeding stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'stock' => 3,
        'availability_status' => ProductStatusEnum::IN_STOCK,
    ]);

    $user->cartItems()->create([
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $this->actingAs($user)
        ->post(route('cart.store', $product), ['quantity' => 5])
        ->assertRedirect();

    expect($user->cartItems()->whereBelongsTo($product)->first())
        ->quantity->toBe(3);
});

it('updates an existing cart item quantity without exceeding stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'stock' => 4,
        'availability_status' => ProductStatusEnum::IN_STOCK,
    ]);

    $user->cartItems()->create([
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $this->actingAs($user)
        ->patchJson(route('cart.update', $product), ['quantity' => 8])
        ->assertSuccessful()
        ->assertJson([
            'cart_count' => 4,
            'item_quantity' => 4,
            'message' => 'Cart updated.',
        ]);

    expect($user->cartItems()->whereBelongsTo($product)->first())
        ->quantity->toBe(4);
});

it('requires at least one item when updating cart quantity', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'stock' => 4,
        'availability_status' => ProductStatusEnum::IN_STOCK,
    ]);

    $this->actingAs($user)
        ->patchJson(route('cart.update', $product), ['quantity' => 0])
        ->assertUnprocessable();
});

it('removes a product from the cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'stock' => 4,
        'availability_status' => ProductStatusEnum::IN_STOCK,
    ]);
    $otherProduct = Product::factory()->create([
        'stock' => 4,
        'availability_status' => ProductStatusEnum::IN_STOCK,
    ]);

    $user->cartItems()->create([
        'product_id' => $product->id,
        'quantity' => 2,
    ]);
    $user->cartItems()->create([
        'product_id' => $otherProduct->id,
        'quantity' => 3,
    ]);

    $this->actingAs($user)
        ->deleteJson(route('cart.destroy', $product))
        ->assertSuccessful()
        ->assertJson([
            'cart_count' => 3,
            'item_quantity' => 0,
            'message' => 'Product removed from cart.',
        ]);

    expect($user->cartItems()->whereBelongsTo($product)->exists())->toBeFalse()
        ->and($user->cartItems()->whereBelongsTo($otherProduct)->first())
        ->quantity->toBe(3);
});

it('does not add out of stock products', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'stock' => 0,
        'availability_status' => ProductStatusEnum::NO_STOCK,
    ]);

    $this->actingAs($user)
        ->post(route('cart.store', $product))
        ->assertRedirect()
        ->assertSessionHas('cart-error');

    expect($user->cartItems)->toHaveCount(0);
});

it('requires authentication to add products to the cart', function () {
    $product = Product::factory()->create();

    $this->postJson(route('cart.store', $product))
        ->assertUnauthorized();
});
