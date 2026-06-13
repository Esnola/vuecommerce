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

            $now = now();

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
                $orderedUrls = collect([
                    $productData['thumbnail'] ?? null,
                    ...($productData['images'] ?? []),
                ])->filter()->unique()->values();

                foreach ($orderedUrls as $index => $url) {
                    $productImages[] = [
                        'product_id' => $productId,
                        'url' => $url,
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
