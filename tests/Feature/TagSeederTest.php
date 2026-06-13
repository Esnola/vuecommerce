<?php

use Database\Seeders\CategorySeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('does not seed tags with the same slug as their category', function () {
    $this->seed([
        CategorySeeder::class,
        TagSeeder::class,
    ]);

    $matchingTags = DB::table('tags')
        ->join('categories', 'categories.id', '=', 'tags.category_id')
        ->whereColumn('tags.slug', 'categories.slug')
        ->count();

    expect($matchingTags)->toBe(0);
});

it('removes an existing tag with the same slug as its category', function () {
    $this->seed(CategorySeeder::class);

    $category = DB::table('categories')
        ->where('slug', 'mens-shirts')
        ->firstOrFail();

    DB::table('tags')->insert([
        'category_id' => $category->id,
        'name' => "men's shirts",
        'slug' => 'mens-shirts',
        'active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->seed(TagSeeder::class);

    expect(
        DB::table('tags')
            ->where('category_id', $category->id)
            ->where('slug', $category->slug)
            ->exists()
    )->toBeFalse();
});
