<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class TagSeeder extends Seeder
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

        foreach ($data['categories'] as $categoryData) {
            $categorySlug = Str::slug($categoryData['category']);
            $categoryId = DB::table('categories')
                ->where('slug', $categorySlug)
                ->value('id');

            if ($categoryId === null) {
                throw new RuntimeException(
                    "Category [{$categoryData['category']}] must be seeded before its tags."
                );
            }

            DB::table('tags')
                ->where('category_id', $categoryId)
                ->where('slug', $categorySlug)
                ->delete();

            $now = now();

            foreach ($categoryData['tags'] as $tag) {
                $tagSlug = Str::slug($tag);

                if ($tagSlug === $categorySlug) {
                    continue;
                }

                DB::table('tags')->updateOrInsert(
                    [
                        'category_id' => $categoryId,
                        'slug' => $tagSlug,
                    ],
                    fn (bool $exists) => [
                        'name' => $tag,
                        'active' => true,
                        'deleted_at' => null,
                        'updated_at' => $now,
                        ...($exists ? [] : ['created_at' => $now]),
                    ]
                );
            }
        }
    }
}
