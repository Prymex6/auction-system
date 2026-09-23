<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\ImageUpload;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Review;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\Watchlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_account_requires_correct_password()
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/profile', ['password' => 'zle-haslo'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
    }

    public function test_delete_account_anonymizes_personal_data_and_soft_deletes()
    {
        Storage::fake('public');
        $avatarPath = 'avatars/test-avatar.jpg';
        Storage::disk('public')->put($avatarPath, 'fake-content');

        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'phone' => '123456789',
            'bio' => 'O mnie...',
            'address' => 'Testowa 1',
            'city' => 'Warszawa',
            'postcode' => '00-001',
            'country' => 'Polska',
            'avatar' => $avatarPath,
            'is_public' => true,
        ]);
        ImageUpload::create(['path' => $avatarPath, 'user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/profile', ['password' => 'password']);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertNotNull($user->deleted_at);
        $this->assertSame('Użytkownik usunięty', $user->name);
        $this->assertNull($user->first_name);
        $this->assertNull($user->last_name);
        $this->assertNull($user->phone);
        $this->assertNull($user->bio);
        $this->assertNull($user->address);
        $this->assertNull($user->city);
        $this->assertNull($user->postcode);
        $this->assertNull($user->country);
        $this->assertNull($user->avatar);
        $this->assertFalse((bool) $user->is_public);
        $this->assertStringContainsString('usuniety+'.$user->id.'@', $user->email);

        Storage::disk('public')->assertMissing($avatarPath);
        $this->assertDatabaseMissing('image_uploads', ['path' => $avatarPath]);
    }

    public function test_delete_account_invalidates_all_tokens()
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $token = $user->createToken('test-token')->plainTextToken;

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/profile', ['password' => 'password'])
            ->assertStatus(200);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_deleted_user_cannot_log_in_again()
    {
        $user = User::factory()->create(['name' => 'usuwany_user', 'password' => Hash::make('password')]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/profile', ['password' => 'password'])
            ->assertStatus(200);

        $this->postJson('/api/auth/login', [
            'login' => 'usuwany_user',
            'password' => 'password',
        ])->assertStatus(401);
    }

    public function test_delete_account_removes_purely_personal_data()
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $auction = Auction::factory()->create();
        Watchlist::create(['user_id' => $user->id, 'auction_id' => $auction->id]);
        Notification::create([
            'user_id' => $user->id,
            'type' => 'test',
            'title' => 'Test',
            'message' => 'Test message',
        ]);
        UserDevice::create([
            'user_id' => $user->id,
            'device_token' => 'test-device-token',
            'device_name' => 'Test Device',
            'device_type' => 'desktop',
            'ip_address' => '127.0.0.1',
            'last_activity_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/profile', ['password' => 'password'])
            ->assertStatus(200);

        $this->assertDatabaseMissing('watchlists', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('notifications', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('user_devices', ['user_id' => $user->id]);
    }

    public function test_auction_still_displays_anonymized_seller_after_account_deletion()
    {
        $seller = User::factory()->create(['password' => Hash::make('password')]);
        $auction = Auction::factory()->create(['user_id' => $seller->id]);

        $this->actingAs($seller, 'sanctum')
            ->deleteJson('/api/profile', ['password' => 'password'])
            ->assertStatus(200);

        $auction->refresh();
        $this->assertNotNull($auction->seller);
        $this->assertSame('Użytkownik usunięty', $auction->seller->name);
    }

    public function test_message_still_displays_anonymized_sender_after_account_deletion()
    {
        $sender = User::factory()->create(['password' => Hash::make('password')]);
        $recipient = User::factory()->create();
        $message = Message::factory()->create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
        ]);

        $this->actingAs($sender, 'sanctum')
            ->deleteJson('/api/profile', ['password' => 'password'])
            ->assertStatus(200);

        $message->refresh();
        $this->assertNotNull($message->sender);
        $this->assertSame('Użytkownik usunięty', $message->sender->name);
    }

    public function test_review_still_displays_anonymized_author_after_account_deletion()
    {
        $author = User::factory()->create(['password' => Hash::make('password')]);
        $target = User::factory()->create();
        $review = Review::factory()->create([
            'from_user_id' => $author->id,
            'to_user_id' => $target->id,
        ]);

        $this->actingAs($author, 'sanctum')
            ->deleteJson('/api/profile', ['password' => 'password'])
            ->assertStatus(200);

        $review->refresh();
        $this->assertNotNull($review->from);
        $this->assertSame('Użytkownik usunięty', $review->from->name);
    }

    public function test_bid_still_displays_anonymized_bidder_after_account_deletion()
    {
        $bidder = User::factory()->create(['password' => Hash::make('password')]);
        $auction = Auction::factory()->create();
        $bid = Bid::create([
            'auction_id' => $auction->id,
            'user_id' => $bidder->id,
            'amount' => 150,
        ]);

        $this->actingAs($bidder, 'sanctum')
            ->deleteJson('/api/profile', ['password' => 'password'])
            ->assertStatus(200);

        $bid->refresh();
        $this->assertNotNull($bid->user);
        $this->assertSame('Użytkownik usunięty', $bid->user->name);
    }
}
