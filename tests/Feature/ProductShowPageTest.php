<?php

use App\Enums\ProductStatusEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows product database details on the product page', function () {
    $product = Product::factory()->create([
        'title' => 'Professional Desk Lamp',
        'slug' => 'professional-desk-lamp',
        'description' => 'A precise task light for focused work.',
        'brand' => 'North Studio',
        'sku' => 'LAMP-001',
        'price' => 129.95,
        'discount_percentage' => 15,
        'on_offer' => true,
        'stock' => 12,
        'minimum_order_quantity' => 2,
        'weight' => 1.25,
        'dimensions' => [
            'width' => 18,
            'height' => 42,
            'depth' => 18,
        ],
        'availability_status' => ProductStatusEnum::IN_STOCK,
        'shipping_information' => 'Ships in 48 hours.',
        'warranty_information' => 'Two year warranty.',
        'return_policy' => '30 day returns.',
    ]);
    ProductImage::factory()->for($product)->create([
        'url' => 'https://example.com/lamp.jpg',
        'position' => 1,
    ]);
    $category = Category::query()->create([
        'name' => 'Lighting',
        'slug' => 'lighting',
    ]);
    $tag = Tag::query()->create([
        'category_id' => $category->id,
        'name' => 'Office',
        'slug' => 'office',
    ]);

    $product->categories()->attach($category);
    $product->tags()->attach($tag);

    $user = User::factory()->create();
    $user->cartItems()->create([
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    $this->actingAs($user)
        ->get(route('products.show', $product->slug))
        ->assertSuccessful()
        ->assertSee('Professional Desk Lamp')
        ->assertSee('North Studio')
        ->assertSee('LAMP-001')
        ->assertSee('129,95 €')
        ->assertSee('15,00 %')
        ->assertSee('A precise task light for focused work.')
        ->assertSee('Ships in 48 hours.')
        ->assertSee('Two year warranty.')
        ->assertSee('30 day returns.')
        ->assertSee('Lighting')
        ->assertSee('Office')
        ->assertSee('18 cm x 42 cm x 18 cm')
        ->assertSee('1.25 kg')
        ->assertSee('Add to cart')
        ->assertSee('In cart:', false)
        ->assertSee('data-cart-quantity-form', false)
        ->assertSee('data-cart-remove-button', false)
        ->assertSee('Remove')
        ->assertSee(route('cart.update', $product), false)
        ->assertSee(route('cart.destroy', $product), false)
        ->assertSee('max="12"', false)
        ->assertSee('value="3"', false)
        ->assertDontSee('Multiple strap configurations')
        ->assertDontSee('Washed Black');
});
