<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('stores unique favorite relationships', function () {
    expect(Schema::hasTable('favorites'))->toBeTrue()
        ->and(Schema::hasColumns('favorites', [
            'user_id',
            'product_id',
            'created_at',
            'updated_at',
        ]))->toBeTrue();

    $user = User::factory()->create();
    $product = Product::factory()->create();

    $user->favoriteProducts()->syncWithoutDetaching([$product->id]);
    $user->favoriteProducts()->syncWithoutDetaching([$product->id]);

    expect($user->favoriteProducts)->toHaveCount(1)
        ->and($product->favoritedByUsers()->first()->is($user))->toBeTrue();
});

it('toggles a favorite for an authenticated user', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)
        ->postJson(route('favorites.toggle', $product))
        ->assertSuccessful()
        ->assertJson(['is_favorite' => true]);

    expect($user->favoriteProducts()->whereKey($product->id)->exists())->toBeTrue();

    $this->actingAs($user)
        ->postJson(route('favorites.toggle', $product))
        ->assertSuccessful()
        ->assertJson(['is_favorite' => false]);

    expect($user->favoriteProducts()->whereKey($product->id)->exists())->toBeFalse();
});

it('combines guest favorites with existing database favorites', function () {
    $user = User::factory()->create();
    $existingFavorite = Product::factory()->create();
    $guestFavorite = Product::factory()->create();

    $user->favoriteProducts()->attach($existingFavorite);

    $response = $this->actingAs($user)
        ->postJson(route('favorites.sync'), [
            'product_ids' => [$existingFavorite->id, $guestFavorite->id],
        ])
        ->assertSuccessful()
        ->assertJsonCount(2, 'favorite_ids');

    expect($response->json('favorite_ids'))
        ->toEqualCanonicalizing([$existingFavorite->id, $guestFavorite->id])
        ->and($user->favoriteProducts()->pluck('products.id')->all())
        ->toEqualCanonicalizing([$existingFavorite->id, $guestFavorite->id]);
});

it('requires authentication to persist favorites', function () {
    $product = Product::factory()->create();

    $this->postJson(route('favorites.toggle', $product))
        ->assertUnauthorized();

    $this->postJson(route('favorites.sync'), ['product_ids' => [$product->id]])
        ->assertUnauthorized();
});

it('rejects missing and deleted products during synchronization', function () {
    $user = User::factory()->create();
    $deletedProduct = Product::factory()->create();
    $deletedProduct->delete();

    $this->actingAs($user)
        ->postJson(route('favorites.sync'), [
            'product_ids' => [$deletedProduct->id, 999999],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['product_ids.0', 'product_ids.1']);
});
