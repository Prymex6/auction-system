<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'john_doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id', 'name', 'email', 'created_at',
                ],
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'john_doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    /** @test */
    public function user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
                'token',
            ]);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    /** @test */
    public function user_cannot_login_with_wrong_password()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Niepoprawne dane logowania']);
    }

    /** @test */
    public function banned_user_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'is_banned' => true,
            'ban_reason' => 'Spam',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Twoje konto zostało zablokowane',
                'reason' => 'Spam',
            ]);
    }

    /** @test */
    public function user_can_get_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Wylogowanie pomyślne']);
    }

    /** @test */
    public function unauthenticated_request_to_protected_api_route_returns_401_json_regardless_of_accept_header()
    {
        $response = $this->get('/api/profile', ['Accept' => 'text/html']);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Musisz być zalogowany']);
    }

    /** @test */
    public function unauthenticated_request_to_protected_api_route_returns_401_json_with_no_accept_header_at_all()
    {
        $response = $this->call('GET', '/api/profile', server: ['HTTP_ACCEPT' => '']);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Musisz być zalogowany']);
    }
}
