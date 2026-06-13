<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class ProductRelationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productsData = $this->readJson('products.json')['products'];
        $categoriesData = $this->readJson('categories_tags.json')['categories'];

        DB::transaction(function () use ($productsData, $categoriesData) {
            $products = DB::table('products')
                ->whereIn('sku', array_column($productsData, 'sku'))
                ->pluck('id', 'sku');

            $categories = DB::table('categories')->pluck('id', 'slug');
            $tags = DB::table('tags')
                ->get(['id', 'category_id', 'slug'])
                ->keyBy(fn ($tag) => "{$tag->category_id}:{$tag->slug}");

            $categoryTags = collect($categoriesData)->mapWithKeys(
                fn (array $category) => [
                    Str::slug($category['category']) => $category['tags'],
                ]
            );

            $productIds = [];
            $productCategories = [];
            $productTags = [];
            $now = now();

            foreach ($productsData as $productData) {
                $productId = $products->get($productData['sku']);
                $categorySlug = Str::slug($productData['category']);
                $categoryId = $categories->get($categorySlug);

                if ($productId === null) {
                    throw new RuntimeException(
                        "Product with SKU [{$productData['sku']}] must be seeded first."
                    );
                }

                if ($categoryId === null) {
                    throw new RuntimeException(
                        "Category [{$productData['category']}] must be seeded first."
                    );
                }

                $productIds[] = $productId;
                $productCategories[] = [
                    'product_id' => $productId,
                    'category_id' => $categoryId,
                ];

                $availableTags = collect($categoryTags->get($categorySlug, []))
                    ->reject(
                        fn (string $tagName) => Str::slug($tagName) === $categorySlug
                    );

                $tagCount = min($availableTags->count(), 4);

                foreach ($availableTags->take($tagCount) as $tagName) {
                    $tagKey = "{$categoryId}:".Str::slug($tagName);
                    $tag = $tags->get($tagKey);

                    if ($tag === null) {
                        throw new RuntimeException(
                            "Tag [{$tagName}] for category [{$productData['category']}] must be seeded first."
                        );
                    }

                    $productTags[] = [
                        'product_id' => $productId,
                        'tag_id' => $tag->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            DB::table('product_tags')->whereIn('product_id', $productIds)->delete();
            DB::table('product_categories')->whereIn('product_id', $productIds)->delete();

            DB::table('product_categories')->insert($productCategories);

            foreach (array_chunk($productTags, 500) as $chunk) {
                DB::table('product_tags')->insert($chunk);
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function readJson(string $filename): array
    {
        return json_decode(
            File::get(database_path("data/{$filename}")),
            true,
            flags: JSON_THROW_ON_ERROR
        );
    }
}
