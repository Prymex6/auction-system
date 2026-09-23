<?php

namespace Tests\Feature;

use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageViewTrackingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_can_track_a_page_view_without_authentication()
    {
        $response = $this->postJson('/api/track', ['path' => '/auctions/1']);

        $response->assertStatus(204);
        $this->assertDatabaseHas('page_views', ['path' => '/auctions/1']);
    }

    /** @test */
    public function tracking_never_stores_raw_ip_or_user_agent()
    {
        $this->postJson('/api/track', ['path' => '/']);

        $pageView = PageView::first();
        $this->assertNotNull($pageView->visitor_hash);
        $this->assertEquals(64, strlen($pageView->visitor_hash));
        $this->assertArrayNotHasKey('ip', $pageView->getAttributes());
        $this->assertArrayNotHasKey('user_agent', $pageView->getAttributes());
    }

    /** @test */
    public function same_visitor_same_day_produces_the_same_hash()
    {
        $this->withHeaders(['User-Agent' => 'TestAgent/1.0'])
            ->postJson('/api/track', ['path' => '/page-a']);
        $this->withHeaders(['User-Agent' => 'TestAgent/1.0'])
            ->postJson('/api/track', ['path' => '/page-b']);

        $hashes = PageView::pluck('visitor_hash')->unique();
        $this->assertCount(1, $hashes);
    }

    /** @test */
    public function admin_panel_paths_are_not_tracked()
    {
        $response = $this->postJson('/api/track', ['path' => '/admin']);

        $response->assertStatus(204);
        $this->assertDatabaseCount('page_views', 0);
    }

    /** @test */
    public function path_is_required()
    {
        $response = $this->postJson('/api/track', []);

        $response->assertStatus(422);
    }
}
