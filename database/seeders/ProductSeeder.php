<?php
  
  namespace Database\Seeders;
  
  use App\Models\Product;
  use Illuminate\Database\Seeder;
  use Illuminate\Support\Facades\Http;
  
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
        Product::updateOrCreate(
          [
            'title' => $product['title'],
            'description' => $product['description'] ?? null,
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
  }
