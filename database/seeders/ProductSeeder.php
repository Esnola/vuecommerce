<?php
  
  namespace Database\Seeders;
  
  use App\Models\Product;
  use Illuminate\Database\Seeder;
  use Illuminate\Support\Facades\Http;
  use Illuminate\Support\Str;
  
  class ProductSeeder extends Seeder
  {
    public function run(): void
    {
      $products = Http::retry(3, 500)
        ->timeout(30)
        ->get('https://dummyjson.com/products', [
          'limit' => 0,
        ])
        ->throw()
        ->json('products');
      
      foreach ($products as $product) {
        $existingProduct = Product::withTrashed()
          ->where('title', $product['title'])
          ->first();

        Product::withTrashed()->updateOrCreate(
          ['title' => $product['title']],
          [
            'deleted_at' => null,
            'description' => $product['description'] ?? null,
            'slug' => $this->uniqueSlug(
              $product['title'],
              $existingProduct?->id
            ),
            'category' => $product['category'],
            'price' => $product['price'],
            'discount_percentage' =>
              $product['discountPercentage'] ?? 0,
            'rating' => $product['rating'] ?? 0,
            'stock' => $product['stock'] ?? 0,
            'tags' => $product['tags'] ?? [],
            'brand' => $product['brand'] ?? null,
            'sku' => $product['sku'],
            'weight' => $product['weight'] ?? null,
            'dimensions' => $product['dimensions'] ?? null,
            'warranty_information' =>
              $product['warrantyInformation'] ?? null,
            'shipping_information' =>
              $product['shippingInformation'] ?? null,
            'availability_status' =>
              $product['availabilityStatus'] ?? null,
            'reviews' => $product['reviews'] ?? [],
            'return_policy' =>
              $product['returnPolicy'] ?? null,
            'minimum_order_quantity' =>
              $product['minimumOrderQuantity'] ?? 1,
            'meta' => $product['meta'] ?? null,
            'images' => $product['images'] ?? [],
            'thumbnail' => $product['thumbnail'] ?? null,
          ]
        );
      }
    }

    private function uniqueSlug(
      string $title,
      ?int $productId = null
    ): string
    {
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
        $slug = $baseSlug . '-' . $suffix;
        $suffix++;
      }

      return $slug;
    }
  }
