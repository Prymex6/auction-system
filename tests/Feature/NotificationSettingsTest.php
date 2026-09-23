<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\NotificationContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    private NotificationContentService $service;

    private User $user;

    private Auction $auction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NotificationContentService::class);
        $this->user = User::factory()->create();
        $this->auction = Auction::factory()->create(['final_bid_amount' => 500]);
    }

    public function test_outbid_notification_skipped_when_disabled()
    {
        PlatformSetting::updateOrCreate([], ['send_outbid_notification' => false]);

        $this->service->notifyOutbid($this->user, $this->auction, 100);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_outbid_notification_sent_when_enabled()
    {
        PlatformSetting::updateOrCreate([], ['send_outbid_notification' => true]);

        $this->service->notifyOutbid($this->user, $this->auction, 100);

        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_auction_ending_notification_skipped_when_disabled()
    {
        PlatformSetting::updateOrCreate([], ['send_auction_ending_notification' => false]);

        $this->service->notifyAuctionEnded($this->user, $this->auction);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_won_auction_notification_skipped_when_disabled()
    {
        PlatformSetting::updateOrCreate([], ['send_won_auction_notification' => false]);

        $this->service->notifyWonAuction($this->user, $this->auction);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_notifications_sent_by_default_when_no_settings_row()
    {
        $this->service->notifyOutbid($this->user, $this->auction, 100);
        $this->service->notifyAuctionEnded($this->user, $this->auction);
        $this->service->notifyWonAuction($this->user, $this->auction);

        $this->assertDatabaseCount('notifications', 3);
    }
}
