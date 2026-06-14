<?php

namespace Database\Seeders;

use App\Enums\ProductStatusEnum;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = json_decode(
            File::get(database_path('data/products.json')),
            true,
            flags: JSON_THROW_ON_ERROR
        )['products'];

        foreach ($products as $product) {
            $existingProduct = Product::withTrashed()
                ->where('sku', $product['sku'])
                ->first();

            Product::withTrashed()->updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'title' => $product['title'],
                    'deleted_at' => null,
                    'description' => $product['description'] ?? null,
                    'slug' => $this->uniqueSlug(
                        $product['title'],
                        $existingProduct?->id
                    ),
                    'price' => $product['price'],
                    'discount_percentage' => $product['discountPercentage'] ?? 0,
                    'stock' => $product['stock'] ?? 0,
                    'brand' => $product['brand'] ?? null,
                    'weight' => $product['weight'] ?? null,
                    'dimensions' => $product['dimensions'] ?? null,
                    'warranty_information' => $product['warrantyInformation'] ?? null,
                    'shipping_information' => $product['shippingInformation'] ?? null,
                    'availability_status' => $product['availabilityStatus']
                      ?? ProductStatusEnum::IN_STOCK->value,
                    'return_policy' => $product['returnPolicy'] ?? null,
                    'on_offer' => rand(0, 1),
                    'minimum_order_quantity' => $product['minimumOrderQuantity'] ?? 1,
                    'meta' => $product['meta'] ?? null,
                ]
            );
        }
    }

    private function uniqueSlug(
        string $title,
        ?int $productId = null
    ): string {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $suffix = 2;

        while (Product::withTrashed()
            ->where('slug', $slug)
            ->when(
                $productId,
                fn ($query) => $query->whereKeyNot($productId)
            )
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
