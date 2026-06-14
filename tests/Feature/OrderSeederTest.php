<?php

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\OrderItemSeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\ReviewSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates delivered orders and items from product reviews', function () {
    $this->seed(DatabaseSeeder::class);

    $firstFiftyUserIds = User::query()
        ->orderBy('id')
        ->limit(50)
        ->pluck('id');

    expect(Order::query()->count())
        ->toBe(Review::query()->distinct()->count('user_id'))
        ->and(OrderItem::query()->count())
        ->toBe(Review::query()->count())
        ->and(Order::query()->whereNotIn('created_by', $firstFiftyUserIds)->exists())
        ->toBeFalse()
        ->and(OrderItem::query()->whereNotIn('user_id', $firstFiftyUserIds)->exists())
        ->toBeFalse();

    Order::query()
        ->with(['items', 'buyer'])
        ->each(function (Order $order) {
            expect($order->status)->toBe(OrderStatusEnum::DELIVERED)
                ->and($order->buyer)->not->toBeNull()
                ->and($order->items)->not->toBeEmpty()
                ->and(round((float) $order->total_price, 2))
                ->toBe(round((float) $order->items->sum('total_price'), 2));

            $order->items->each(function (OrderItem $item) use ($order) {
                $expectedTotal = round(
                    $item->quantity
                    * (float) $item->unit_price
                    * (1 - ((float) $item->discount_percentage / 100)),
                    2
                );

                expect($item->user_id)->toBe($order->created_by)
                    ->and((float) $item->total_price)->toBe($expectedTotal);
            });
        });
});

it('can seed orders and order items repeatedly without duplicates', function () {
    $this->seed(DatabaseSeeder::class);

    $counts = [
        'orders' => Order::query()->count(),
        'order_items' => OrderItem::query()->count(),
    ];

    $this->seed(DatabaseSeeder::class);

    expect([
        'orders' => Order::query()->count(),
        'order_items' => OrderItem::query()->count(),
    ])->toBe($counts);
});

it('only assigns seeded commerce data to the first fifty users', function () {
    User::factory()->count(60)->create();

    $this->seed([
        ProductSeeder::class,
        ReviewSeeder::class,
        OrderSeeder::class,
        OrderItemSeeder::class,
    ]);

    $firstFiftyUserIds = User::query()
        ->orderBy('id')
        ->limit(50)
        ->pluck('id');

    expect(User::query()->count())->toBe(60)
        ->and(Review::query()->whereNotIn('user_id', $firstFiftyUserIds)->exists())
        ->toBeFalse()
        ->and(Order::query()->whereNotIn('created_by', $firstFiftyUserIds)->exists())
        ->toBeFalse()
        ->and(OrderItem::query()->whereNotIn('user_id', $firstFiftyUserIds)->exists())
        ->toBeFalse();
});
