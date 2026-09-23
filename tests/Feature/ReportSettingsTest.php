<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_are_rejected_when_disabled_in_settings()
    {
        PlatformSetting::updateOrCreate([], ['enable_user_reports' => false]);
        $reporter = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);
        $auction = Auction::factory()->create();

        $response = $this->actingAs($reporter, 'sanctum')->postJson('/api/reports', [
            'auction_id' => $auction->id,
            'reason' => 'spam',
            'description' => 'To jest testowe zgloszenie z opisem dluzszym niz 10 znakow.',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_seller_is_auto_banned_after_reaching_report_threshold()
    {
        PlatformSetting::updateOrCreate([], ['auto_ban_reports_threshold' => 2, 'auto_ban_duration_hours' => 48]);
        $seller = User::factory()->create(['is_active' => true]);
        $auction = Auction::factory()->create(['user_id' => $seller->id]);

        $reporter1 = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);
        $reporter2 = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);

        $this->actingAs($reporter1, 'sanctum')->postJson('/api/reports', [
            'auction_id' => $auction->id,
            'reason' => 'spam',
            'description' => 'Pierwsze zgloszenie z wystarczajaco dlugim opisem.',
        ])->assertStatus(201);

        $this->assertFalse($seller->fresh()->is_banned);

        $this->actingAs($reporter2, 'sanctum')->postJson('/api/reports', [
            'auction_id' => $auction->id,
            'reason' => 'fraud',
            'description' => 'Drugie zgloszenie z wystarczajaco dlugim opisem.',
        ])->assertStatus(201);

        $seller->refresh();
        $this->assertTrue($seller->is_banned);
        $this->assertNotNull($seller->ban_until);
    }

    public function test_seller_not_auto_banned_when_threshold_not_configured()
    {
        $seller = User::factory()->create(['is_active' => true]);
        $auction = Auction::factory()->create(['user_id' => $seller->id]);
        $reporter = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);

        $this->actingAs($reporter, 'sanctum')->postJson('/api/reports', [
            'auction_id' => $auction->id,
            'reason' => 'spam',
            'description' => 'Zgloszenie bez skonfigurowanego progu auto-bana.',
        ])->assertStatus(201);

        $this->assertFalse($seller->fresh()->is_banned);
    }
}
