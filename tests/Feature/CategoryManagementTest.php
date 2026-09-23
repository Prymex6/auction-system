<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
    }

    public function test_admin_creates_smart_category_with_full_rule()
    {
        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/admin/categories', [
            'name' => 'Rocznik 2026',
            'description' => 'Młode gołębie z rocznika 2026',
            'category_type' => 'smart',
            'is_featured' => true,
            'smart_filter' => [
                'type' => 'auction',
                'year' => 2026,
                'sort_by' => 'bids_count_desc',
            ],
        ]);

        $response->assertSuccessful();

        $category = Category::where('name', 'Rocznik 2026')->first();
        $this->assertNotNull($category);
        $this->assertEquals('auction', $category->smart_filter['type']);
        $this->assertEquals(2026, $category->smart_filter['year']);
        $this->assertEquals('bids_count_desc', $category->smart_filter['sort_by']);
    }

    public function test_empty_rule_fields_are_stripped()
    {
        $this->actingAs($this->admin, 'sanctum')->postJson('/api/admin/categories', [
            'name' => 'Czysta reguła',
            'category_type' => 'smart',
            'smart_filter' => [
                'type' => 'buy_now',
                'year' => null,
                'breed' => '',
                'sort_by' => 'latest',
            ],
        ])->assertSuccessful();

        $filter = Category::where('name', 'Czysta reguła')->first()->smart_filter;
        $this->assertArrayNotHasKey('year', $filter);
        $this->assertArrayNotHasKey('breed', $filter);
        $this->assertEquals('buy_now', $filter['type']);
    }

    public function test_invalid_sort_by_is_rejected()
    {
        $this->actingAs($this->admin, 'sanctum')->postJson('/api/admin/categories', [
            'name' => 'Zła reguła',
            'category_type' => 'smart',
            'smart_filter' => ['sort_by' => 'hackerskie_sortowanie'],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['smart_filter.sort_by']);
    }

    public function test_smart_category_count_is_dynamic()
    {
        $seller = User::factory()->create(['is_active' => true]);
        Auction::factory()->count(3)->create([
            'user_id' => $seller->id,
            'type' => 'buy_now',
            'status' => 'active',
            'ends_at' => now()->addDays(5),
        ]);
        Auction::factory()->create([
            'user_id' => $seller->id,
            'type' => 'auction',
            'status' => 'active',
            'ends_at' => now()->addDays(5),
        ]);

        Category::create([
            'name' => 'Kup teraz test',
            'slug' => 'kup-teraz-test',
            'category_type' => 'smart',
            'smart_filter' => ['type' => 'buy_now', 'sort_by' => 'latest'],
            'is_featured' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/admin/categories');
        $response->assertStatus(200);

        $row = collect($response->json('data'))->firstWhere('name', 'Kup teraz test');
        $this->assertEquals(3, $row['auctions_count']);
    }

    public function test_updated_rule_changes_category_content()
    {
        $seller = User::factory()->create(['is_active' => true]);
        Auction::factory()->create([
            'user_id' => $seller->id,
            'type' => 'auction',
            'year' => 2024,
            'status' => 'active',
            'ends_at' => now()->addDays(5),
        ]);
        Auction::factory()->count(2)->create([
            'user_id' => $seller->id,
            'type' => 'auction',
            'year' => 2026,
            'status' => 'active',
            'ends_at' => now()->addDays(5),
        ]);

        $category = Category::create([
            'name' => 'Roczniki',
            'slug' => 'roczniki',
            'category_type' => 'smart',
            'smart_filter' => ['year' => 2024, 'sort_by' => 'latest'],
        ]);

        $this->assertEquals(1, $category->getSmartAuctions()->count());

        $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/categories/{$category->id}", [
                'smart_filter' => ['year' => 2026, 'sort_by' => 'latest'],
            ])->assertStatus(200);

        $this->assertEquals(2, $category->fresh()->getSmartAuctions()->count());
    }
}
