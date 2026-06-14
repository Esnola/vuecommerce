<?php

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('forbids guests from viewing orders', function () {
    $this->get(route('orders.index'))->assertRedirect(route('login'));
});

it('forbids non admin users from viewing orders', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('orders.index'))
        ->assertForbidden();
});

it('allows admins to view all order details', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $buyer = User::factory()->create([
        'first_name' => 'Ana',
        'last_name' => 'Garcia',
        'email' => 'ana@example.com',
        'phone' => '600123123',
    ]);
    $product = Product::factory()->create([
        'title' => 'Premium Keyboard',
        'slug' => 'premium-keyboard',
        'sku' => 'KEY-001',
    ]);
    $order = Order::factory()->create([
        'created_by' => $buyer,
        'updated_by' => $buyer,
        'status' => OrderStatusEnum::DELIVERED,
        'total_price' => 179.98,
        'created_at' => '2026-06-15 10:30:00',
    ]);

    OrderItem::factory()->create([
        'order_id' => $order,
        'user_id' => $buyer,
        'product_id' => $product,
        'quantity' => 2,
        'unit_price' => 99.99,
        'discount_percentage' => 10,
        'total_price' => 179.98,
    ]);

    $this->actingAs($admin)
        ->get(route('orders.index'))
        ->assertSuccessful()
        ->assertSee("#{$order->id}")
        ->assertSee('Ana Garcia')
        ->assertSee('ana@example.com')
        ->assertSee('600123123')
        ->assertSee('Premium Keyboard')
        ->assertSee('KEY-001')
        ->assertSee('179,98')
        ->assertSee('Delivered');
});

it('only shows the orders navigation link to admins', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($admin)
        ->get(route('pages.index'))
        ->assertSee(route('orders.index'));

    $this->actingAs($user)
        ->get(route('pages.index'))
        ->assertDontSee(route('orders.index'));
});
