<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productsData = json_decode(
            File::get(database_path('data/products.json')),
            true,
            flags: JSON_THROW_ON_ERROR
        )['products'];

        DB::transaction(function () use ($productsData) {
            $this->ensureUserPool($productsData);

            $users = User::query()
                ->orderBy('id')
                ->limit(50)
                ->get();

            if ($users->count() < 50) {
                throw new RuntimeException(
                    'At least 50 users are required to seed reviews.'
                );
            }

            $seededReviewIds = [];
            $reviewIndex = 0;

            foreach ($productsData as $productData) {
                $product = Product::query()
                    ->where('sku', $productData['sku'])
                    ->first();

                if ($product === null) {
                    throw new RuntimeException(
                        "Product with SKU [{$productData['sku']}] must be seeded first."
                    );
                }

                foreach ($productData['reviews'] ?? [] as $reviewData) {
                    $user = $users[$reviewIndex % $users->count()];

                    $review = Review::query()->updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'user_id' => $user->id,
                        ],
                        [
                            'comment' => $reviewData['comment'],
                            'rating' => $reviewData['rating'],
                            'created_at' => $reviewData['date'],
                            'updated_at' => $reviewData['date'],
                        ]
                    );

                    $seededReviewIds[] = $review->id;
                    $reviewIndex++;
                }
            }

            Review::query()
                ->whereNotIn('id', $seededReviewIds)
                ->delete();
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $productsData
     */
    private function ensureUserPool(array $productsData): void
    {
        $requiredUsers = 50 - User::query()->count();

        if ($requiredUsers <= 0) {
            return;
        }

        $reviewers = collect($productsData)
            ->flatMap(fn (array $product): array => $product['reviews'] ?? [])
            ->unique('reviewerEmail')
            ->values();

        $existingEmails = User::query()->pluck('email');

        $reviewers
            ->reject(
                fn (array $reviewer): bool => $existingEmails
                    ->contains($reviewer['reviewerEmail'])
            )
            ->take($requiredUsers)
            ->each(function (array $reviewer): void {
                [$firstName, $lastName] = $this->splitReviewerName(
                    $reviewer['reviewerName']
                );

                User::query()->create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $reviewer['reviewerEmail'],
                    'status' => 'active',
                    'password' => Str::password(32),
                    'email_verified_at' => now(),
                ]);
            });
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitReviewerName(string $reviewerName): array
    {
        $nameParts = preg_split('/\s+/', trim($reviewerName), 2);

        return [
            $nameParts[0],
            $nameParts[1] ?? '',
        ];
    }
}
