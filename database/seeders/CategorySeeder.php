<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Racing Pigeons', 'description' => 'Szybkie gołębie wyścigowe'],
            ['name' => 'Show Pigeons', 'description' => 'Gołębie wystawowe'],
            ['name' => 'Roller Pigeons', 'description' => 'Gołębie akrobatyczne'],
            ['name' => 'Fancy Pigeons', 'description' => 'Ozdobne gołębie'],
            ['name' => 'Utility Pigeons', 'description' => 'Gołębie użytkowe'],
            ['name' => 'Exotic Species', 'description' => 'Egzotyczne gatunki gołębi'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                [
                    'slug' => str($category['name'])->slug(),
                    'description' => $category['description'],
                ]
            );
        }
    }
}
