<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * (OR, nie mutually exclusive z is_featured) logika na /api/categories/featured.
 */
class CategoryHomeSectionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
    }

    public function test_admin_can_create_category_with_show_as_home_section()
    {
        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/admin/categories', [
            'name' => 'Linia Hurrican 51',
            'category_type' => 'smart',
            'show_as_home_section' => true,
            'smart_filter' => ['sort_by' => 'latest'],
        ]);

        $response->assertSuccessful();

        $category = Category::where('name', 'Linia Hurrican 51')->first();
        $this->assertNotNull($category);
        $this->assertTrue($category->show_as_home_section);
    }

    public function test_admin_can_update_show_as_home_section()
    {
        $category = Category::create([
            'name' => 'Testowa',
            'slug' => 'testowa',
            'category_type' => 'smart',
            'smart_filter' => ['sort_by' => 'latest'],
            'show_as_home_section' => false,
        ]);

        $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/categories/{$category->id}", [
                'show_as_home_section' => true,
            ])->assertStatus(200);

        $this->assertTrue($category->fresh()->show_as_home_section);
    }

    public function test_admin_categories_index_returns_show_as_home_section_flag()
    {
        Category::create([
            'name' => 'Sekcja Home',
            'slug' => 'sekcja-home',
            'category_type' => 'smart',
            'smart_filter' => ['sort_by' => 'latest'],
            'show_as_home_section' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/admin/categories');
        $response->assertStatus(200);

        $row = collect($response->json('data'))->firstWhere('name', 'Sekcja Home');
        $this->assertNotNull($row);
        $this->assertTrue($row['show_as_home_section']);
    }

    public function test_featured_endpoint_includes_category_with_only_show_as_home_section()
    {
        Category::create([
            'name' => 'Tylko sekcja',
            'slug' => 'tylko-sekcja',
            'category_type' => 'smart',
            'smart_filter' => ['sort_by' => 'latest'],
            'is_featured' => false,
            'show_as_home_section' => true,
        ]);

        $response = $this->getJson('/api/categories/featured');
        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Tylko sekcja'));
    }

    public function test_featured_endpoint_includes_category_with_only_is_featured()
    {
        Category::create([
            'name' => 'Tylko wyróżniona',
            'slug' => 'tylko-wyroz',
            'category_type' => 'smart',
            'smart_filter' => ['sort_by' => 'latest'],
            'is_featured' => true,
            'show_as_home_section' => false,
        ]);

        $response = $this->getJson('/api/categories/featured');
        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Tylko wyróżniona'));
    }

    public function test_category_with_both_flags_appears_once_not_duplicated()
    {
        Category::create([
            'name' => 'Oba flagi',
            'slug' => 'oba-flagi',
            'category_type' => 'smart',
            'smart_filter' => ['sort_by' => 'latest'],
            'is_featured' => true,
            'show_as_home_section' => true,
        ]);

        $response = $this->getJson('/api/categories/featured');
        $response->assertStatus(200);

        $matches = collect($response->json('data'))->where('name', 'Oba flagi');
        $this->assertCount(1, $matches);
    }

    public function test_featured_endpoint_excludes_category_with_neither_flag()
    {
        Category::create([
            'name' => 'Zwykła',
            'slug' => 'zwykla',
            'category_type' => 'smart',
            'smart_filter' => ['sort_by' => 'latest'],
            'is_featured' => false,
            'show_as_home_section' => false,
        ]);

        $response = $this->getJson('/api/categories/featured');
        $response->assertStatus(200);

        $names = collect($response->json('data'))->pluck('name');
        $this->assertFalse($names->contains('Zwykła'));
    }

    public function test_featured_endpoint_returns_matching_auctions_for_home_section_category()
    {
        $seller = User::factory()->create(['is_active' => true]);
        Auction::factory()->count(2)->create([
            'user_id' => $seller->id,
            'type' => 'buy_now',
            'status' => 'active',
            'ends_at' => now()->addDays(5),
        ]);

        Category::create([
            'name' => 'Kup teraz sekcja',
            'slug' => 'kup-teraz-sekcja',
            'category_type' => 'smart',
            'smart_filter' => ['type' => 'buy_now', 'sort_by' => 'latest'],
            'show_as_home_section' => true,
        ]);

        $response = $this->getJson('/api/categories/featured');
        $response->assertStatus(200);

        $row = collect($response->json('data'))->firstWhere('name', 'Kup teraz sekcja');
        $this->assertCount(2, $row['auctions']);
    }
}
