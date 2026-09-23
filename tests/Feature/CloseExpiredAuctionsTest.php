<?php

namespace Tests\Feature;

use App\Jobs\CloseExpiredAuctions;
use App\Models\Auction;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CloseExpiredAuctionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_licytacja_is_closed()
    {
        $category = Category::factory()->create();
        $auction = Auction::factory()->create([
            'category_id' => $category->id,
            'type' => 'auction',
            'status' => 'active',
            'ends_at' => now()->subMinute(),
        ]);

        (new CloseExpiredAuctions)->handle();

        $this->assertDatabaseHas('auctions', ['id' => $auction->id, 'status' => 'ended']);
    }

    public function test_expired_buy_now_auction_is_not_closed()
    {
        $category = Category::factory()->create();
        $auction = Auction::factory()->create([
            'category_id' => $category->id,
            'type' => 'buy_now',
            'status' => 'active',
            'ends_at' => now()->subMonth(),
        ]);

        (new CloseExpiredAuctions)->handle();

        $this->assertDatabaseHas('auctions', ['id' => $auction->id, 'status' => 'active']);
    }

    public function test_expired_both_type_auction_is_closed_like_licytacja()
    {
        // traktowany jak "auction", nie jak "buy_now" (ta sama logika co w
        // update()/complete()).
        $category = Category::factory()->create();
        $auction = Auction::factory()->create([
            'category_id' => $category->id,
            'type' => 'both',
            'status' => 'active',
            'ends_at' => now()->subMinute(),
        ]);

        (new CloseExpiredAuctions)->handle();

        $this->assertDatabaseHas('auctions', ['id' => $auction->id, 'status' => 'ended']);
    }

    public function test_not_yet_expired_buy_now_auction_stays_active_too()
    {
        $category = Category::factory()->create();
        $auction = Auction::factory()->create([
            'category_id' => $category->id,
            'type' => 'buy_now',
            'status' => 'active',
            'ends_at' => now()->addDays(7),
        ]);

        (new CloseExpiredAuctions)->handle();

        $this->assertDatabaseHas('auctions', ['id' => $auction->id, 'status' => 'active']);
    }
}
