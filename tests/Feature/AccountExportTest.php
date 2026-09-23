<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\Message;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_export_data()
    {
        $this->getJson('/api/profile/export')->assertStatus(401);
    }

    public function test_user_can_export_own_data_as_downloadable_json()
    {
        $user = User::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'phone' => '123456789',
        ]);
        $auction = Auction::factory()->create(['user_id' => $user->id, 'title' => 'Moj golab']);
        $other = User::factory()->create();
        Message::factory()->create([
            'sender_id' => $user->id,
            'recipient_id' => $other->id,
            'content' => 'Tresc testowa',
        ]);
        Review::factory()->create([
            'from_user_id' => $other->id,
            'to_user_id' => $user->id,
            'auction_id' => $auction->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/profile/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));

        $data = $response->json();
        $this->assertSame($user->id, $data['konto']['id']);
        $this->assertSame('Jan', $data['konto']['imie']);
        $this->assertSame('123456789', $data['konto']['telefon']);
        $this->assertCount(1, $data['aukcje_wystawione']);
        $this->assertSame('Moj golab', $data['aukcje_wystawione'][0]['title']);
        $this->assertCount(1, $data['wiadomosci_wyslane']);
        $this->assertCount(1, $data['recenzje_otrzymane']);
    }

    public function test_export_does_not_leak_other_users_data()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Auction::factory()->create(['user_id' => $other->id, 'title' => 'Cudza aukcja']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/profile/export');

        $data = $response->json();
        $this->assertCount(0, $data['aukcje_wystawione']);
    }
}
