<?php

namespace Tests\Feature\Admin;

use App\Models\Auction;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAdvancedTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $regularUser;

    private User $banUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->regularUser = User::factory()->create();
        $this->banUser = User::factory()->create();
    }

    // ===== USER MANAGEMENT TESTS =====

    /** @test */
    public function admin_can_view_all_users()
    {
        User::factory(5)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'auctions_count',
                        'reputation',
                        'is_banned',
                        'created_at',
                    ],
                ],
            ]);

        $this->assertCount(8, $response->json('data')); // 5 + regularUser + banUser + admin
    }

    /** @test */
    public function admin_can_edit_user_details()
    {
        $response = $this->actingAs($this->admin)
            ->patchJson("/api/admin/users/{$this->regularUser->id}", [
                'name' => 'new_name123',
                'email' => 'newemail@example.com',
            ]);

        $response->assertStatus(200);

        $this->regularUser->refresh();
        $this->assertEquals('new_name123', $this->regularUser->name);
        $this->assertEquals('newemail@example.com', $this->regularUser->email);
    }

    /** @test */
    public function reputation_is_not_manually_settable_and_is_computed_live_from_reviews()
    {
        $response = $this->actingAs($this->admin)
            ->patchJson("/api/admin/users/{$this->regularUser->id}", [
                'reputation' => 999,
            ]);

        $response->assertStatus(200);
        $this->assertEquals(0, $this->regularUser->fresh()->reputation);

        $reviewer = User::factory()->create();
        Review::create([
            'from_user_id' => $reviewer->id,
            'to_user_id' => $this->regularUser->id,
            'rating' => 5,
            'comment' => 'Świetny hodowca',
        ]);

        $this->assertEquals(5, $this->regularUser->fresh()->reputation);
    }

    /** @test */
    public function admin_can_upload_avatar_for_user_and_it_actually_persists()
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin)
            ->patch("/api/admin/users/{$this->regularUser->id}", [
                'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $this->regularUser->refresh();
        $this->assertNotNull($this->regularUser->avatar);
        $this->assertDatabaseHas('users', [
            'id' => $this->regularUser->id,
            'avatar' => $this->regularUser->avatar,
        ]);
        Storage::disk('public')->assertExists($this->regularUser->avatar);
    }

    /** @test */
    public function admin_cannot_set_user_name_to_duplicate_of_another_user()
    {
        $response = $this->actingAs($this->admin)
            ->patchJson("/api/admin/users/{$this->regularUser->id}", [
                'name' => $this->admin->name,
            ]);

        $response->assertStatus(422);
        $this->assertNotEquals($this->admin->name, $this->regularUser->fresh()->name);
    }

    /** @test */
    public function admin_cannot_set_user_name_with_invalid_characters()
    {
        $response = $this->actingAs($this->admin)
            ->patchJson("/api/admin/users/{$this->regularUser->id}", [
                'name' => 'imie z odstepem',
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function admin_can_ban_user_temporarily()
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/users/{$this->banUser->id}/ban-temporary", [
                'reason' => 'Violating platform rules',
                'hours' => 24,
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'ban_until']);

        $this->banUser->refresh();
        $this->assertTrue($this->banUser->is_banned);
        $this->assertEquals('Violating platform rules', $this->banUser->ban_reason);
        $this->assertNotNull($this->banUser->ban_until);

        // Check ban_until is approximately 24 hours from now
        $expectedTime = now()->addHours(24);
        $this->assertTrue($this->banUser->ban_until->diffInMinutes($expectedTime) <= 5);
    }

    /** @test */
    public function admin_can_ban_user_for_specific_hours()
    {
        $hours = 48;

        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/users/{$this->banUser->id}/ban-temporary", [
                'reason' => 'Spam content',
                'hours' => $hours,
            ]);

        $response->assertStatus(200);

        $this->banUser->refresh();
        $expectedTime = now()->addHours($hours);
        $this->assertTrue($this->banUser->ban_until->diffInMinutes($expectedTime) <= 5);
    }

    /** @test */
    public function temporary_ban_is_logged_in_audit_logs()
    {
        $this->actingAs($this->admin)
            ->postJson("/api/admin/users/{$this->banUser->id}/ban-temporary", [
                'reason' => 'Test ban',
                'hours' => 24,
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'admin_user_id' => $this->admin->id,
            'action' => 'banned_temporary',
            'model_type' => 'User',
            'model_id' => $this->banUser->id,
        ]);
    }

    // ===== AUCTION MANAGEMENT TESTS =====

    /** @test */
    public function admin_can_view_all_auctions()
    {
        $user = User::factory()->create();
        Auction::factory(3)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/auctions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'start_price',
                        'current_price',
                        'status',
                        'created_at',
                    ],
                ],
            ]);
    }

    /** @test */
    public function admin_can_edit_auction()
    {
        $user = User::factory()->create();
        $auction = Auction::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'current_price' => 100,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/admin/auctions/{$auction->id}", [
                'status' => 'active',
                'current_price' => 150,
            ]);

        $response->assertStatus(200);

        $auction->refresh();
        $this->assertEquals('active', $auction->status);
        $this->assertEquals(150, $auction->current_price);
    }

    /** @test */
    public function admin_can_edit_pigeon_details_of_auction_and_they_actually_persist()
    {
        $user = User::factory()->create();
        $auction = Auction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Stary tytul',
            'breed' => 'Janssen',
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/admin/auctions/{$auction->id}", [
                'title' => 'Nowy tytul po edycji',
                'breed' => 'Sion',
                'year' => 2023,
                'gender' => 'samica',
                'color' => 'Czarna',
                'size' => 'duzy',
                'description' => 'Zaktualizowany opis golebia',
            ]);

        $response->assertStatus(200);

        $auction->refresh();
        $this->assertEquals('Nowy tytul po edycji', $auction->title);
        $this->assertEquals('Sion', $auction->breed);
        $this->assertEquals(2023, $auction->year);
        $this->assertEquals('samica', $auction->gender);
        $this->assertEquals('Czarna', $auction->color);
        $this->assertEquals('duzy', $auction->size);
        $this->assertEquals('Zaktualizowany opis golebia', $auction->description);
    }

    /** @test */
    public function admin_can_delete_auction()
    {
        $user = User::factory()->create();
        $auction = Auction::factory()->create([
            'user_id' => $user->id,
        ]);

        $auctionId = $auction->id;

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/admin/auctions/{$auctionId}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('auctions', ['id' => $auctionId]);
    }

    /** @test */
    public function admin_can_approve_auction()
    {
        $user = User::factory()->create();
        $auction = Auction::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/auctions/{$auction->id}/approve");

        $response->assertStatus(200);

        $auction->refresh();
        $this->assertEquals('active', $auction->status);
    }

    /** @test */
    public function admin_can_reject_auction()
    {
        $user = User::factory()->create();
        $auction = Auction::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/auctions/{$auction->id}/reject", [
                'reason' => 'Fraudulent listing',
            ]);

        $response->assertStatus(200);

        $auction->refresh();
        $this->assertEquals('cancelled', $auction->status);
    }

    // ===== REPORT MANAGEMENT TESTS =====

    /** @test */
    public function admin_can_view_all_reports()
    {
        $reporter = User::factory()->create();
        $reportedUser = User::factory()->create();

        Report::factory(3)->create([
            'reported_by' => $reporter->id,
            'reportable_type' => User::class,
            'reportable_id' => $reportedUser->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/reports');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'reason',
                        'description',
                        'status',
                        'created_at',
                    ],
                ],
            ]);
    }

    /** @test */
    public function admin_can_get_report_details()
    {
        $reporter = User::factory()->create();
        $reportedUser = User::factory()->create();

        $report = Report::factory()->create([
            'reported_by' => $reporter->id,
            'reportable_type' => User::class,
            'reportable_id' => $reportedUser->id,
            'reason' => 'spam',
            'description' => 'Spam message',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/reports/{$report->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $report->id,
                    'reason' => 'spam',
                    'status' => 'pending',
                ],
            ]);
    }

    /** @test */
    public function admin_can_update_report_status()
    {
        $reporter = User::factory()->create();
        $reportedUser = User::factory()->create();

        $report = Report::factory()->create([
            'reported_by' => $reporter->id,
            'reportable_type' => User::class,
            'reportable_id' => $reportedUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/admin/reports/{$report->id}", [
                'status' => 'reviewing',
                'notes' => 'Investigating this report',
            ]);

        $response->assertStatus(200);

        $report->refresh();
        $this->assertEquals('reviewing', $report->status);
        $this->assertEquals('Investigating this report', $report->notes);
    }

    /** @test */
    public function admin_can_resolve_report_and_ban_user()
    {
        $reporter = User::factory()->create();
        $reportedUser = User::factory()->create();
        $auction = Auction::factory()->create(['user_id' => $reportedUser->id]);

        $report = Report::factory()->create([
            'reported_by' => $reporter->id,
            'reportable_type' => Auction::class,
            'reportable_id' => $auction->id,
            'reason' => 'fraud',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/admin/reports/{$report->id}", [
                'status' => 'resolved',
                'notes' => 'User banned for fraudulent activity',
                'ban_user' => true,
                'ban_hours' => 72,
                'ban_reason' => 'Fraudulent activity - from report',
            ]);

        $response->assertStatus(200);

        $report->refresh();
        $this->assertEquals('resolved', $report->status);
        $this->assertNotNull($report->reviewed_at);

        $reportedUser->refresh();
        $this->assertNotNull($reportedUser->ban_until);
        $this->assertTrue($reportedUser->is_banned);
        $this->assertTrue($reportedUser->isBanned());

        // Check ban is for approximately 72 hours
        $expectedTime = now()->addHours(72);
        $this->assertTrue($reportedUser->ban_until->diffInMinutes($expectedTime) <= 5);
    }

    /** @test */
    public function admin_can_dismiss_report()
    {
        $reporter = User::factory()->create();
        $reportedUser = User::factory()->create();

        $report = Report::factory()->create([
            'reported_by' => $reporter->id,
            'reportable_type' => User::class,
            'reportable_id' => $reportedUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/admin/reports/{$report->id}", [
                'status' => 'dismissed',
                'notes' => 'False report',
            ]);

        $response->assertStatus(200);

        $report->refresh();
        $this->assertEquals('dismissed', $report->status);
    }

    /** @test */
    public function report_resolution_is_logged_in_audit()
    {
        $reporter = User::factory()->create();
        $reportedUser = User::factory()->create();

        $report = Report::factory()->create([
            'reported_by' => $reporter->id,
            'reportable_type' => User::class,
            'reportable_id' => $reportedUser->id,
        ]);

        $this->actingAs($this->admin)
            ->patchJson("/api/admin/reports/{$report->id}", [
                'status' => 'resolved',
                'notes' => 'User banned',
                'ban_user' => true,
                'ban_hours' => 24,
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'admin_user_id' => $this->admin->id,
            'action' => 'updated',
            'model_type' => 'Report',
            'model_id' => $report->id,
        ]);
    }

    // ===== AUTHORIZATION TESTS =====

    /** @test */
    public function non_admin_cannot_edit_user()
    {
        $response = $this->actingAs($this->regularUser)
            ->patchJson("/api/admin/users/{$this->banUser->id}", [
                'name' => 'Hacked',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function non_admin_cannot_delete_auction()
    {
        $user = User::factory()->create();
        $auction = Auction::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($this->regularUser)
            ->deleteJson("/api/admin/auctions/{$auction->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function non_admin_cannot_manage_reports()
    {
        $reporter = User::factory()->create();
        $reportedUser = User::factory()->create();

        $report = Report::factory()->create([
            'reported_by' => $reporter->id,
            'reportable_type' => User::class,
            'reportable_id' => $reportedUser->id,
        ]);

        $response = $this->actingAs($this->regularUser)
            ->patchJson("/api/admin/reports/{$report->id}", [
                'status' => 'resolved',
            ]);

        $response->assertStatus(403);
    }
}
