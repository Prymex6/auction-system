<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_user(): void
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

        $response->assertStatus(201);
        $response->assertJsonStructure(['message', 'user', 'token']);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'john_doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user->terms_accepted_at);
    }

    public function test_register_requires_terms_acceptance(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'bez_zgody',
            'first_name' => 'Bez',
            'last_name' => 'Zgody',
            'email' => 'bezzgody@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['terms']);
        $this->assertDatabaseMissing('users', ['email' => 'bezzgody@example.com']);
    }

    public function test_register_rejects_explicit_false_terms(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'odrzucil_zgode',
            'first_name' => 'Odrzucil',
            'last_name' => 'Zgode',
            'email' => 'odrzucil@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => false,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['terms']);
    }

    public function test_register_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'jane_doe',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422); // Validation error
    }

    /**
     * Test logowania
     */
    public function test_login_user(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'user', 'token']);
    }

    public function test_login_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Niepoprawne dane logowania']);
    }
}
