<?php

use App\Enums\UserStatusEnum;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $this->get(route('cart.index'))
        ->assertRedirect(route('login'));
});

it('forbids users whose account cannot be accessed', function (UserStatusEnum $status, bool $verified) {
    $user = User::factory()->create([
        'email_verified_at' => $verified ? now() : null,
        'status' => $status,
    ]);

    $this->actingAs($user)
        ->get(route('cart.index'))
        ->assertForbidden();
})->with([
    'pending' => [UserStatusEnum::PENDING, true],
    'unverified' => [UserStatusEnum::ACTIVE, false],
    'suspended' => [UserStatusEnum::SUSPEND, true],
]);

it('only displays cart items belonging to the authenticated user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $product = Product::factory()->create([
        'title' => 'Cart Keyboard',
        'slug' => 'cart-keyboard',
        'price' => 99.99,
    ]);
    $otherProduct = Product::factory()->create([
        'title' => 'Private Monitor',
        'slug' => 'private-monitor',
    ]);

    $user->cartItems()->create([
        'product_id' => $product->id,
        'quantity' => 2,
    ]);
    $otherUser->cartItems()->create([
        'product_id' => $otherProduct->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->get(route('cart.index'))
        ->assertSuccessful()
        ->assertSee('Shopping cart')
        ->assertSee('Cart Keyboard')
        ->assertSee('199,98 €')
        ->assertSee(route('products.show', $product->slug))
        ->assertDontSee('Private Monitor');
});

it('updates item quantities from the cart page', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 3]);
    $cartItem = $user->cartItems()->create([
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::cart.index')
        ->call('updateQuantity', $cartItem->id, 10)
        ->assertSee('3 items');

    expect($cartItem->fresh()->quantity)->toBe(3);
});

it('removes an item from the cart page', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'title' => 'Removable Cart Item',
    ]);
    $cartItem = $user->cartItems()->create([
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::cart.index')
        ->assertSee('Removable Cart Item')
        ->call('removeItem', $cartItem->id)
        ->assertDontSee('Removable Cart Item')
        ->assertSee('Your cart is empty.');

    expect($cartItem->fresh())->toBeNull();
});

it('clears the cart', function () {
    $user = User::factory()->create();
    $user->cartItems()->create([
        'product_id' => Product::factory()->create()->id,
        'quantity' => 1,
    ]);
    $user->cartItems()->create([
        'product_id' => Product::factory()->create()->id,
        'quantity' => 2,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::cart.index')
        ->call('clearCart')
        ->assertSee('Your cart is empty.');

    expect($user->cartItems)->toHaveCount(0);
});

it('shows an empty state and catalog link', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('cart.index'))
        ->assertSuccessful()
        ->assertSee('Your cart is empty.')
        ->assertSee(route('products.index'));
});

it('shows cart links to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('pages.index'))
        ->assertSee(route('cart.index'));
});
