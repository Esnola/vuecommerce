<?php

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

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
        ->assertSee('Delivered')
        ->assertSee('data-order="'.$order->id.'"', false)
        ->assertSee('<details', false)
        ->assertSee('<summary', false);
});

it('does not show the orders navigation link in the header', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($admin)
        ->get(route('pages.index'))
        ->assertDontSee(route('orders.index'));

    $this->actingAs($user)
        ->get(route('pages.index'))
        ->assertDontSee(route('orders.index'));
});

it('allows admins to filter orders by buyer', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $selectedBuyer = User::factory()->create([
        'first_name' => 'Selected',
        'last_name' => 'Buyer',
        'email' => 'selected@example.com',
    ]);
    $otherBuyer = User::factory()->create([
        'first_name' => 'Other',
        'last_name' => 'Buyer',
        'email' => 'other@example.com',
    ]);
    $selectedOrder = Order::factory()->create(['created_by' => $selectedBuyer]);
    $otherOrder = Order::factory()->create(['created_by' => $otherBuyer]);

    $this->actingAs($admin);

    Livewire::test('pages::orders.index')
        ->assertSee('Filter by user')
        ->assertSee('Selected Buyer')
        ->assertSee('Other Buyer')
        ->set('selectedBuyerId', (string) $selectedBuyer->id)
        ->assertSee("#{$selectedOrder->id}")
        ->assertDontSee("#{$otherOrder->id}");
});

it('allows admins to sort orders by total amount', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $buyer = User::factory()->create();
    $lowestOrder = Order::factory()->create([
        'created_by' => $buyer,
        'total_price' => 10,
    ]);
    $middleOrder = Order::factory()->create([
        'created_by' => $buyer,
        'total_price' => 50,
    ]);
    $highestOrder = Order::factory()->create([
        'created_by' => $buyer,
        'total_price' => 100,
    ]);

    $this->actingAs($admin);

    Livewire::test('pages::orders.index')
        ->set('sortBy', 'total_price')
        ->set('sortDirection', 'asc')
        ->assertSeeInOrder(["#{$lowestOrder->id}", "#{$middleOrder->id}", "#{$highestOrder->id}"])
        ->set('sortDirection', 'desc')
        ->assertSeeInOrder(["#{$highestOrder->id}", "#{$middleOrder->id}", "#{$lowestOrder->id}"]);
});

it('allows admins to sort orders by the buyers order count', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $frequentBuyer = User::factory()->create();
    $occasionalBuyer = User::factory()->create();
    $frequentBuyerOrders = Order::factory()
        ->count(3)
        ->create(['created_by' => $frequentBuyer]);
    $occasionalBuyerOrder = Order::factory()->create(['created_by' => $occasionalBuyer]);
    $frequentBuyerOrder = $frequentBuyerOrders->sortByDesc('id')->firstOrFail();

    $this->actingAs($admin);

    Livewire::test('pages::orders.index')
        ->set('sortBy', 'buyer_orders_count')
        ->set('sortDirection', 'asc')
        ->assertSeeInOrder(["#{$occasionalBuyerOrder->id}", "#{$frequentBuyerOrder->id}"])
        ->set('sortDirection', 'desc')
        ->assertSeeInOrder(["#{$frequentBuyerOrder->id}", "#{$occasionalBuyerOrder->id}"]);
});

it('allows admins to sort orders by item count', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $buyer = User::factory()->create();
    $product = Product::factory()->create();
    $orderWithOneItem = Order::factory()->create(['created_by' => $buyer]);
    $orderWithTwoItems = Order::factory()->create(['created_by' => $buyer]);
    $orderWithThreeItems = Order::factory()->create(['created_by' => $buyer]);

    foreach ([
        $orderWithOneItem->id => 1,
        $orderWithTwoItems->id => 2,
        $orderWithThreeItems->id => 3,
    ] as $orderId => $itemCount) {
        OrderItem::factory()
            ->count($itemCount)
            ->create([
                'order_id' => $orderId,
                'user_id' => $buyer,
                'product_id' => $product,
            ]);
    }

    $this->actingAs($admin);

    Livewire::test('pages::orders.index')
        ->set('sortBy', 'items_count')
        ->set('sortDirection', 'asc')
        ->assertSeeInOrder(["#{$orderWithOneItem->id}", "#{$orderWithTwoItems->id}", "#{$orderWithThreeItems->id}"])
        ->set('sortDirection', 'desc')
        ->assertSeeInOrder(["#{$orderWithThreeItems->id}", "#{$orderWithTwoItems->id}", "#{$orderWithOneItem->id}"]);
});
