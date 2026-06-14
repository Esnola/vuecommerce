<?php

namespace Database\Seeders;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::query()
            ->orderBy('id')
            ->limit(50)
            ->pluck('id');

        $orders = Order::query()
            ->whereColumn('created_by', 'updated_by')
            ->where('status', OrderStatusEnum::DELIVERED->value)
            ->whereIn('created_by', $userIds)
            ->whereIn(
                'created_by',
                Review::query()->select('user_id')->distinct()
            )
            ->get();

        DB::transaction(function () use ($orders) {
            foreach ($orders as $order) {
                $reviews = Review::query()
                    ->where('user_id', $order->created_by)
                    ->with('product:id,price,discount_percentage')
                    ->get();

                $productIds = $reviews->pluck('product_id');

                $order->items()
                    ->when(
                        $productIds->isNotEmpty(),
                        fn ($query) => $query->whereNotIn('product_id', $productIds),
                        fn ($query) => $query
                    )
                    ->delete();

                foreach ($reviews as $review) {
                    $quantity = ($review->product_id % 3) + 1;
                    $unitPrice = (float) $review->product->price;
                    $discountPercentage = (float) $review->product->discount_percentage;
                    $totalPrice = round(
                        $quantity * $unitPrice * (1 - ($discountPercentage / 100)),
                        2
                    );

                    OrderItem::query()->updateOrCreate(
                        [
                            'order_id' => $order->id,
                            'product_id' => $review->product_id,
                        ],
                        [
                            'user_id' => $order->created_by,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'discount_percentage' => $discountPercentage,
                            'total_price' => $totalPrice,
                            'created_at' => $review->created_at,
                            'updated_at' => $review->created_at,
                        ]
                    );
                }

                $order->update([
                    'total_price' => $order->items()->sum('total_price'),
                ]);
            }
        });
    }
}
