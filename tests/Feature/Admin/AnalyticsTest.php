<?php

namespace Tests\Feature\Admin;

use App\Models\PageView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    /** @test */
    public function non_admin_cannot_view_analytics()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/admin/analytics');

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_view_analytics()
    {
        $response = $this->getJson('/api/admin/analytics');

        $response->assertStatus(401);
    }

    /** @test */
    public function analytics_counts_visits_and_unique_visitors_for_selected_period()
    {
        PageView::create(['path' => '/', 'visitor_hash' => 'hash-a', 'viewed_at' => now()]);
        PageView::create(['path' => '/', 'visitor_hash' => 'hash-a', 'viewed_at' => now()]);
        PageView::create(['path' => '/auctions', 'visitor_hash' => 'hash-b', 'viewed_at' => now()]);
        PageView::create(['path' => '/', 'visitor_hash' => 'hash-c', 'viewed_at' => now()->subDays(10)]);

        $response = $this->actingAs($this->admin)->getJson('/api/admin/analytics?period=7d');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals(3, $data['summary']['visits']['value']);
        $this->assertEquals(2, $data['summary']['unique_visitors']['value']);
    }

    /** @test */
    public function analytics_counts_new_accounts_in_selected_period()
    {
        User::factory()->create(['created_at' => now()]);
        User::factory()->create(['created_at' => now()->subDays(2)]);
        User::factory()->create(['created_at' => now()->subDays(40)]); // poza 30d

        $response = $this->actingAs($this->admin)->getJson('/api/admin/analytics?period=30d');

        $this->assertEquals(3, $response->json('data.summary.new_accounts.value'));
    }

    /** @test */
    public function analytics_returns_daily_time_series_covering_the_whole_period_without_gaps()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/admin/analytics?period=7d');

        $series = $response->json('data.series');
        $this->assertCount(7, $series);
        $this->assertEquals(now()->subDays(6)->toDateString(), $series[0]['date']);
        $this->assertEquals(now()->toDateString(), $series[6]['date']);
    }

    /** @test */
    public function analytics_computes_change_percentage_against_previous_equal_period()
    {
        // Biezacy okres (dzisiaj, period=today): 4 odwiedziny
        PageView::create(['path' => '/', 'visitor_hash' => 'h1', 'viewed_at' => now()]);
        PageView::create(['path' => '/', 'visitor_hash' => 'h2', 'viewed_at' => now()]);
        PageView::create(['path' => '/', 'visitor_hash' => 'h3', 'viewed_at' => now()]);
        PageView::create(['path' => '/', 'visitor_hash' => 'h4', 'viewed_at' => now()]);
        // Poprzedni okres (wczoraj): 2 odwiedziny -> wzrost o 100%
        PageView::create(['path' => '/', 'visitor_hash' => 'h5', 'viewed_at' => now()->subDay()]);
        PageView::create(['path' => '/', 'visitor_hash' => 'h6', 'viewed_at' => now()->subDay()]);

        $response = $this->actingAs($this->admin)->getJson('/api/admin/analytics?period=today');

        $this->assertEquals(4, $response->json('data.summary.visits.value'));
        $this->assertEquals(100.0, $response->json('data.summary.visits.change_pct'));
    }

    /** @test */
    public function analytics_returns_top_pages_ordered_by_views()
    {
        PageView::create(['path' => '/popular', 'visitor_hash' => 'a', 'viewed_at' => now()]);
        PageView::create(['path' => '/popular', 'visitor_hash' => 'b', 'viewed_at' => now()]);
        PageView::create(['path' => '/popular', 'visitor_hash' => 'c', 'viewed_at' => now()]);
        PageView::create(['path' => '/less-popular', 'visitor_hash' => 'd', 'viewed_at' => now()]);

        $response = $this->actingAs($this->admin)->getJson('/api/admin/analytics?period=7d');

        $topPages = $response->json('data.top_pages');
        $this->assertEquals('/popular', $topPages[0]['path']);
        $this->assertEquals(3, $topPages[0]['views']);
    }
}
