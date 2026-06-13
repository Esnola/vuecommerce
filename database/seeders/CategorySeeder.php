<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $data = json_decode(
      File::get(database_path('data/categories_tags.json')),
      true,
      flags: JSON_THROW_ON_ERROR
    );

    foreach ($data['categories'] as $category) {
      $now = now();

      DB::table('categories')->updateOrInsert(
        ['slug' => Str::slug($category['category'])],
        fn (bool $exists) => [
          'name' => $category['category'],
          'active' => true,
          'updated_at' => $now,
          ...($exists ? [] : ['created_at' => $now]),
        ]
      );
    }
  }
}
