<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmartCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // Delete old smart categories
        DB::table('categories')->where('category_type', 'smart')->delete();

        $categories = [
            [
                'name' => 'Młode gołębie '.date('Y'),
                'slug' => 'młode-gołębie-'.date('Y'),
                'description' => 'Gołębie młode z bieżącego rocznika - idealne do hodowli',
                'category_type' => 'smart',
                'smart_filter' => [
                    'type' => 'auction',
                    'year' => (int) date('Y'),
                    'sort_by' => 'bids_count_desc',
                ],
                'is_featured' => true,
            ],
            [
                'name' => 'Najlepsi hodowcy',
                'slug' => 'najlepsi-hodowcy',
                'description' => 'Gołębie od hodowców z najwyższą reputacją',
                'category_type' => 'smart',
                'smart_filter' => [
                    'type' => 'auction',
                    'sort_by' => 'seller_reputation',
                ],
                'is_featured' => true,
            ],
            [
                'name' => 'Najpopularniejsze',
                'slug' => 'najpopularniejsze',
                'description' => 'Aukcje gołębi z największą liczbą licytacji',
                'category_type' => 'smart',
                'smart_filter' => [
                    'type' => 'auction',
                    'sort_by' => 'bids_count_desc',
                ],
                'is_featured' => true,
            ],
            [
                'name' => 'Najdroższe',
                'slug' => 'najdroższe',
                'description' => 'Premium gołębie pocztowe - sortowanie od najwyższej ceny',
                'category_type' => 'smart',
                'smart_filter' => [
                    'type' => 'auction',
                    'sort_by' => 'price_desc',
                ],
                'is_featured' => true,
            ],
            [
                'name' => 'Kończące się teraz',
                'slug' => 'kończące-się-teraz',
                'description' => 'Aukcje gołębi kończące się najszybciej - najpierw te o najkrótszym czasie',
                'category_type' => 'smart',
                'smart_filter' => [
                    'type' => 'auction',
                    'sort_by' => 'ending_soon',
                ],
                'is_featured' => true,
            ],
            [
                'name' => 'Kup teraz',
                'slug' => 'kup-teraz',
                'description' => 'Gołębie dostępne do natychmiastowego zakupu bez licytacji',
                'category_type' => 'smart',
                'smart_filter' => [
                    'type' => 'buy_now',
                    'sort_by' => 'latest',
                ],
                'is_featured' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        $this->command->info('✅ Smart categories created successfully!');
    }
}
