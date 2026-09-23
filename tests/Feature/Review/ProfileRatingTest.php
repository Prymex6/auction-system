<?php

namespace Tests\Feature\Review;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_rate_another_users_profile()
    {
        $rater = User::factory()->create();
        $breeder = User::factory()->create();

        $response = $this->actingAs($rater, 'sanctum')
            ->postJson("/api/users/{$breeder->id}/rate", ['rating' => 4.5]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reviews', [
            'from_user_id' => $rater->id,
            'to_user_id' => $breeder->id,
            'auction_id' => null,
            'rating' => 4.5,
        ]);
        $this->assertEquals(4.5, $response->json('average_rating'));
        $this->assertEquals(1, $response->json('total_reviews'));
    }

    public function test_re_rating_updates_existing_rating_instead_of_creating_duplicate()
    {
        $rater = User::factory()->create();
        $breeder = User::factory()->create();

        $this->actingAs($rater, 'sanctum')->postJson("/api/users/{$breeder->id}/rate", ['rating' => 2]);
        $this->actingAs($rater, 'sanctum')->postJson("/api/users/{$breeder->id}/rate", ['rating' => 5]);

        $this->assertEquals(1, Review::where('from_user_id', $rater->id)->where('to_user_id', $breeder->id)->count());
        $this->assertDatabaseHas('reviews', [
            'from_user_id' => $rater->id,
            'to_user_id' => $breeder->id,
            'rating' => 5,
        ]);
    }

    public function test_user_cannot_rate_own_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/users/{$user->id}/rate", ['rating' => 5]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_rating_must_be_a_half_star_increment()
    {
        $rater = User::factory()->create();
        $breeder = User::factory()->create();

        $response = $this->actingAs($rater, 'sanctum')
            ->postJson("/api/users/{$breeder->id}/rate", ['rating' => 3.3]);

        $response->assertStatus(422);
    }

    public function test_rating_below_half_or_above_five_is_rejected()
    {
        $rater = User::factory()->create();
        $breeder = User::factory()->create();

        $this->actingAs($rater, 'sanctum')
            ->postJson("/api/users/{$breeder->id}/rate", ['rating' => 0])
            ->assertStatus(422);

        $this->actingAs($rater, 'sanctum')
            ->postJson("/api/users/{$breeder->id}/rate", ['rating' => 5.5])
            ->assertStatus(422);
    }

    public function test_guest_cannot_rate_profile()
    {
        $breeder = User::factory()->create();

        $response = $this->postJson("/api/users/{$breeder->id}/rate", ['rating' => 5]);

        $response->assertStatus(401);
    }

    public function test_banned_user_cannot_rate_profile()
    {
        $rater = User::factory()->create(['is_banned' => true, 'ban_until' => now()->addDays(3)]);
        $breeder = User::factory()->create();

        $response = $this->actingAs($rater, 'sanctum')
            ->postJson("/api/users/{$breeder->id}/rate", ['rating' => 5]);

        $response->assertStatus(403);
    }

    public function test_stats_endpoint_reflects_average_of_profile_ratings()
    {
        $breeder = User::factory()->create();
        $r1 = User::factory()->create();
        $r2 = User::factory()->create();

        $this->actingAs($r1, 'sanctum')->postJson("/api/users/{$breeder->id}/rate", ['rating' => 4]);
        $this->actingAs($r2, 'sanctum')->postJson("/api/users/{$breeder->id}/rate", ['rating' => 5]);

        $response = $this->getJson("/api/users/{$breeder->id}/stats");

        $response->assertStatus(200);
        $this->assertEquals(4.5, $response->json('data.average_rating'));
        $this->assertEquals(2, $response->json('data.total_reviews'));
    }
}
