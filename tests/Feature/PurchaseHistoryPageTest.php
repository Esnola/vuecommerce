<?php

use App\Enums\OrderStatusEnum;
use App\Enums\UserStatusEnum;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $this->get(route('purchases.index'))
        ->assertRedirect(route('login'));
});

it('forbids users whose account cannot be accessed', function (UserStatusEnum $status, bool $verified) {
    $user = User::factory()->create([
        'email_verified_at' => $verified ? now() : null,
        'status' => $status,
    ]);

    $this->actingAs($user)
        ->get(route('purchases.index'))
        ->assertForbidden();
})->with([
    'pending' => [UserStatusEnum::PENDING, true],
    'unverified' => [UserStatusEnum::ACTIVE, false],
    'suspended' => [UserStatusEnum::SUSPEND, true],
]);

it('only displays purchases made by the authenticated user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $ownedProduct = Product::factory()->create([
        'title' => 'Owned Keyboard',
        'slug' => 'owned-keyboard',
        'sku' => 'OWN-001',
    ]);
    $otherProduct = Product::factory()->create([
        'title' => 'Private Monitor',
        'slug' => 'private-monitor',
        'sku' => 'OTHER-001',
    ]);
    $ownedOrder = Order::factory()->create([
        'created_by' => $user->id,
        'status' => OrderStatusEnum::DELIVERED,
        'total_price' => 179.98,
        'created_at' => '2026-06-15 10:30:00',
    ]);
    $otherOrder = Order::factory()->create([
        'created_by' => $otherUser->id,
        'status' => OrderStatusEnum::PENDING,
        'total_price' => 999.99,
    ]);
    OrderItem::factory()->create([
        'order_id' => $ownedOrder->id,
        'user_id' => $user->id,
        'product_id' => $ownedProduct->id,
        'quantity' => 2,
        'unit_price' => 99.99,
        'discount_percentage' => 10,
        'total_price' => 179.98,
    ]);

    OrderItem::factory()->create([
        'order_id' => $otherOrder->id,
        'user_id' => $otherUser->id,
        'product_id' => $otherProduct->id,
    ]);

    $this->actingAs($user)
        ->get(route('purchases.index'))
        ->assertSuccessful()
        ->assertSee('Purchase history')
        ->assertSee('Owned Keyboard')
        ->assertSee('OWN-001')
        ->assertSee('179,98')
        ->assertSee('Delivered')
        ->assertDontSee('Private Monitor')
        ->assertDontSee('OTHER-001')
        ->assertDontSee('999,99');
});

it('shows an empty state when the user has no purchases', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('purchases.index'))
        ->assertSuccessful()
        ->assertSee('You have not made any purchases yet.');
});

it('shows the purchases link to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('pages.index'))
        ->assertSee(route('purchases.index'));
});
