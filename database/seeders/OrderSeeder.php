<?php

namespace Database\Seeders;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
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

        $buyers = Review::query()
            ->whereIn('user_id', $userIds)
            ->select('user_id')
            ->selectRaw('MIN(created_at) as first_reviewed_at')
            ->groupBy('user_id')
            ->get();

        DB::transaction(function () use ($buyers, $userIds) {
            Order::query()
                ->whereColumn('created_by', 'updated_by')
                ->where('status', OrderStatusEnum::DELIVERED->value)
                ->whereNotIn('created_by', $userIds)
                ->delete();

            foreach ($buyers as $buyer) {
                Order::query()->firstOrCreate(
                    [
                        'created_by' => $buyer->user_id,
                        'updated_by' => $buyer->user_id,
                        'status' => OrderStatusEnum::DELIVERED->value,
                    ],
                    [
                        'total_price' => 0,
                        'created_at' => $buyer->first_reviewed_at,
                        'updated_at' => $buyer->first_reviewed_at,
                    ]
                );
            }
        });
    }
}
