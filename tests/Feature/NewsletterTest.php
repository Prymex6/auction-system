<?php

namespace Tests\Feature;

use App\Mail\NewsletterSubscribedMail;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_subscribe()
    {
        Mail::fake();

        $this->postJson('/api/newsletter', ['email' => 'hodowca@example.pl'])
            ->assertStatus(201);

        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'hodowca@example.pl']);
        Mail::assertQueued(NewsletterSubscribedMail::class, function ($mail) {
            return $mail->hasTo('hodowca@example.pl');
        });
    }

    public function test_email_is_normalized_and_deduplicated()
    {
        $this->postJson('/api/newsletter', ['email' => 'Hodowca@Example.PL'])->assertStatus(201);
        $this->postJson('/api/newsletter', ['email' => 'hodowca@example.pl'])->assertStatus(200);

        $this->assertEquals(1, NewsletterSubscriber::count());
        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'hodowca@example.pl']);
    }

    public function test_invalid_email_is_rejected_in_polish()
    {
        $response = $this->postJson('/api/newsletter', ['email' => 'nie-email']);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
        $this->assertStringContainsString('adres e-mail', $response->json('message'));
    }

    public function test_signed_unsubscribe_link_removes_address()
    {
        NewsletterSubscriber::create(['email' => 'hodowca@example.pl']);

        $url = URL::signedRoute('newsletter.unsubscribe', ['email' => 'hodowca@example.pl']);

        $this->get($url)->assertStatus(200);

        $this->assertDatabaseMissing('newsletter_subscribers', ['email' => 'hodowca@example.pl']);
    }

    public function test_unsubscribe_requires_valid_signature_regresja_dowolny_email_bez_podpisu()
    {
        // Regresja: dawny endpoint POST /api/newsletter/unsubscribe przyjmowal
        NewsletterSubscriber::create(['email' => 'ofiara@example.pl']);

        $this->get('/newsletter/unsubscribe/ofiara@example.pl')->assertStatus(403);

        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'ofiara@example.pl']);
    }

    public function test_subscriber_list_is_admin_only()
    {
        NewsletterSubscriber::create(['email' => 'hodowca@example.pl']);

        $this->getJson('/api/admin/newsletter')->assertStatus(401);

        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/newsletter')->assertStatus(403);

        // admin widzi
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/newsletter');
        $response->assertStatus(200)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.email', 'hodowca@example.pl');
    }
}
