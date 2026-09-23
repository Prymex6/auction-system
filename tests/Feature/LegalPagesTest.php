<?php

namespace Tests\Feature;

use App\Models\LegalPage;
use App\Models\User;
use Database\Seeders\LegalPagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    private function makePage(array $overrides = []): LegalPage
    {
        return LegalPage::create(array_merge([
            'slug' => 'terms',
            'title' => 'Regulamin serwisu',
            'content' => "## Postanowienia\n1. Punkt pierwszy.",
        ], $overrides));
    }

    public function test_guest_can_list_pages()
    {
        $this->makePage();
        $this->makePage(['slug' => 'privacy', 'title' => 'Polityka prywatności']);

        $response = $this->getJson('/api/pages');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => ['*' => ['slug', 'title', 'updated_at']]]);
    }

    public function test_guest_can_view_page_by_slug()
    {
        $this->makePage();

        $response = $this->getJson('/api/pages/terms');

        $response->assertStatus(200)
            ->assertJsonPath('data.slug', 'terms')
            ->assertJsonPath('data.title', 'Regulamin serwisu');
    }

    public function test_unknown_page_returns_404()
    {
        $this->getJson('/api/pages/nie-istnieje')->assertStatus(404);
    }

    public function test_admin_can_update_page()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->makePage();

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/admin/pages/terms', [
                'title' => 'Nowy regulamin',
                'content' => '## Zmieniona sekcja',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Nowy regulamin');

        $this->assertDatabaseHas('legal_pages', [
            'slug' => 'terms',
            'title' => 'Nowy regulamin',
        ]);

        // Zmiana trafia do audit logu
        $this->assertDatabaseHas('audit_logs', [
            'admin_user_id' => $admin->id,
            'model_type' => 'LegalPage',
        ]);
    }

    public function test_regular_user_cannot_update_page()
    {
        $user = User::factory()->create();
        $this->makePage();

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/admin/pages/terms', ['title' => 'Hack'])
            ->assertStatus(403);

        $this->assertDatabaseHas('legal_pages', ['title' => 'Regulamin serwisu']);
    }

    public function test_guest_cannot_update_page()
    {
        $this->makePage();

        $this->patchJson('/api/admin/pages/terms', ['title' => 'Hack'])
            ->assertStatus(401);
    }

    public function test_update_validates_content_length()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->makePage();

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/admin/pages/terms', ['content' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_seeder_creates_all_seven_pages()
    {
        $this->seed(LegalPagesSeeder::class);

        foreach (['terms', 'privacy', 'cookies', 'help', 'contact', 'faq', 'security'] as $slug) {
            $this->assertDatabaseHas('legal_pages', ['slug' => $slug]);
        }
    }
}
