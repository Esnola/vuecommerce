<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class ProductImageSeeder extends Seeder
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
            $products = DB::table('products')
                ->whereIn('sku', array_column($productsData, 'sku'))
                ->pluck('id', 'sku');

            $urls = collect($productsData)
                ->flatMap(fn (array $product) => [
                    $product['thumbnail'] ?? null,
                    ...($product['images'] ?? []),
                ])
                ->filter()
                ->unique()
                ->values();

            $now = now();

            foreach ($urls->chunk(500) as $chunk) {
                DB::table('images')->upsert(
                    $chunk->map(fn (string $url) => [
                        'url' => $url,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all(),
                    ['url'],
                    ['updated_at']
                );
            }

            $images = DB::table('images')
                ->whereIn('url', $urls)
                ->pluck('id', 'url');

            $productIds = [];
            $productImages = [];

            foreach ($productsData as $productData) {
                $productId = $products->get($productData['sku']);

                if ($productId === null) {
                    throw new RuntimeException(
                        "Product with SKU [{$productData['sku']}] must be seeded first."
                    );
                }

                $productIds[] = $productId;
                $orderedUrls = [
                    $productData['thumbnail'] ?? null,
                    ...($productData['images'] ?? []),
                ];

                foreach (array_values(array_filter($orderedUrls)) as $index => $url) {
                    $imageId = $images->get($url);

                    if ($imageId === null) {
                        throw new RuntimeException(
                            "Image [{$url}] could not be seeded."
                        );
                    }

                    $productImages[] = [
                        'product_id' => $productId,
                        'image_id' => $imageId,
                        'position' => $index + 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            DB::table('product_images')->whereIn('product_id', $productIds)->delete();

            foreach (array_chunk($productImages, 500) as $chunk) {
                DB::table('product_images')->insert($chunk);
            }
        });
    }
}
