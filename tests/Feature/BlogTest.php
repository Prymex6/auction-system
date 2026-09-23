<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\PageView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $this->user = User::factory()->create(['is_admin' => false, 'is_active' => true]);
    }

    public function test_public_index_lists_only_published_posts()
    {
        BlogPost::create([
            'title' => 'Opublikowany',
            'content' => 'Treść artykułu',
            'author_id' => $this->admin->id,
            'published' => true,
            'published_at' => now()->subDay(),
        ]);
        BlogPost::create([
            'title' => 'Szkic',
            'content' => 'Treść szkicu',
            'author_id' => $this->admin->id,
            'published' => false,
        ]);
        BlogPost::create([
            'title' => 'Zaplanowany na przyszłość',
            'content' => 'Treść',
            'author_id' => $this->admin->id,
            'published' => true,
            'published_at' => now()->addDay(),
        ]);

        $response = $this->getJson('/api/blogs');
        $response->assertStatus(200);

        $titles = collect($response->json('data'))->pluck('title');
        $this->assertTrue($titles->contains('Opublikowany'));
        $this->assertFalse($titles->contains('Szkic'));
        $this->assertFalse($titles->contains('Zaplanowany na przyszłość'));
    }

    public function test_public_index_orders_by_most_recent_first()
    {
        BlogPost::create([
            'title' => 'Stary',
            'content' => 'x',
            'author_id' => $this->admin->id,
            'published' => true,
            'published_at' => now()->subDays(5),
        ]);
        BlogPost::create([
            'title' => 'Nowy',
            'content' => 'x',
            'author_id' => $this->admin->id,
            'published' => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/blogs');
        $titles = collect($response->json('data'))->pluck('title')->values();
        $this->assertEquals('Nowy', $titles[0]);
        $this->assertEquals('Stary', $titles[1]);
    }

    public function test_show_returns_published_post_by_slug()
    {
        $post = BlogPost::create([
            'title' => 'Artykuł testowy',
            'content' => 'Treść testowa',
            'author_id' => $this->admin->id,
            'published' => true,
            'published_at' => now()->subHour(),
        ]);

        $response = $this->getJson("/api/blogs/{$post->slug}");
        $response->assertStatus(200);
        $this->assertEquals('Artykuł testowy', $response->json('data.title'));
    }

    public function test_show_does_not_increment_raw_views_column_anymore()
    {
        $post = BlogPost::create([
            'title' => 'Licznik odsłon',
            'content' => 'Treść',
            'author_id' => $this->admin->id,
            'published' => true,
            'published_at' => now()->subHour(),
            'views' => 0,
        ]);

        $this->getJson("/api/blogs/{$post->slug}")->assertStatus(200);
        $this->getJson("/api/blogs/{$post->slug}")->assertStatus(200);

        $this->assertEquals(0, $post->getRawOriginal('views'));
    }

    public function test_views_are_computed_as_distinct_visitors_from_page_view()
    {
        // RODO PageView co statystyki admina (dystynkt visitor_hash per
        $post = BlogPost::create([
            'title' => 'Realne odslony',
            'content' => 'Treść',
            'author_id' => $this->admin->id,
            'published' => true,
            'published_at' => now()->subHour(),
        ]);

        PageView::create(['path' => "/blog/{$post->slug}", 'visitor_hash' => 'visitor-a', 'viewed_at' => now()]);
        PageView::create(['path' => "/blog/{$post->slug}", 'visitor_hash' => 'visitor-a', 'viewed_at' => now()]); // ten sam odwiedzajacy - odswiezenie
        PageView::create(['path' => "/blog/{$post->slug}", 'visitor_hash' => 'visitor-b', 'viewed_at' => now()]);
        PageView::create(['path' => '/blog/inny-artykul', 'visitor_hash' => 'visitor-c', 'viewed_at' => now()]);

        $response = $this->getJson("/api/blogs/{$post->slug}");

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.views'));
    }

    public function test_show_returns_404_for_unpublished_post()
    {
        $post = BlogPost::create([
            'title' => 'Niepublikowany',
            'content' => 'Treść',
            'author_id' => $this->admin->id,
            'published' => false,
        ]);

        $this->getJson("/api/blogs/{$post->slug}")->assertStatus(404);
    }

    public function test_show_returns_404_for_post_scheduled_in_future()
    {
        $post = BlogPost::create([
            'title' => 'Z przyszłości',
            'content' => 'Treść',
            'author_id' => $this->admin->id,
            'published' => true,
            'published_at' => now()->addDay(),
        ]);

        $this->getJson("/api/blogs/{$post->slug}")->assertStatus(404);
    }

    public function test_admin_can_create_post()
    {
        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/admin/blogs', [
            'title' => 'Nowy artykuł administratora',
            'content' => 'Treść artykułu administratora',
            'published' => true,
        ]);

        $response->assertStatus(201);

        $post = BlogPost::where('title', 'Nowy artykuł administratora')->first();
        $this->assertNotNull($post);
        $this->assertEquals('nowy-artykul-administratora', $post->slug);
        $this->assertEquals($this->admin->id, $post->author_id);
        $this->assertNotNull($post->published_at);
    }

    public function test_regular_user_cannot_create_post()
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/admin/blogs', [
            'title' => 'Próba użytkownika',
            'content' => 'Treść',
        ]);

        $response->assertStatus(403);
        $this->assertNull(BlogPost::where('title', 'Próba użytkownika')->first());
    }

    public function test_guest_cannot_create_post()
    {
        $this->postJson('/api/admin/blogs', [
            'title' => 'Próba gościa',
            'content' => 'Treść',
        ])->assertStatus(401);
    }

    public function test_admin_can_update_post_and_slug_regenerates()
    {
        $post = BlogPost::create([
            'title' => 'Tytuł oryginalny',
            'content' => 'Treść',
            'author_id' => $this->admin->id,
            'published' => false,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/blogs/{$post->slug}", [
                'title' => 'Tytuł zmieniony',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('tytul-zmieniony', $post->fresh()->slug);
    }

    public function test_regular_user_cannot_update_post()
    {
        $post = BlogPost::create([
            'title' => 'Chroniony',
            'content' => 'Treść',
            'author_id' => $this->admin->id,
            'published' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/admin/blogs/{$post->slug}", ['title' => 'Zhakowany'])
            ->assertStatus(403);

        $this->assertEquals('Chroniony', $post->fresh()->title);
    }

    public function test_admin_can_delete_post()
    {
        $post = BlogPost::create([
            'title' => 'Do usunięcia',
            'content' => 'Treść',
            'author_id' => $this->admin->id,
            'published' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/admin/blogs/{$post->slug}")
            ->assertStatus(200);

        $this->assertNull(BlogPost::find($post->id));
    }

    public function test_admin_can_set_category_and_image_when_creating_post()
    {
        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/admin/blogs', [
            'title' => 'Artykul z kategoria i obrazkiem',
            'content' => 'Tresc',
            'category' => 'Poradnik',
            'image' => 'https://example.com/image.jpg',
            'published' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.category', 'Poradnik')
            ->assertJsonPath('data.image', 'https://example.com/image.jpg');

        $this->assertDatabaseHas('blog_posts', [
            'title' => 'Artykul z kategoria i obrazkiem',
            'category' => 'Poradnik',
            'image' => 'https://example.com/image.jpg',
        ]);
    }

    public function test_regular_user_cannot_delete_post()
    {
        $post = BlogPost::create([
            'title' => 'Nie do usunięcia',
            'content' => 'Treść',
            'author_id' => $this->admin->id,
            'published' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/admin/blogs/{$post->slug}")
            ->assertStatus(403);

        $this->assertNotNull(BlogPost::find($post->id));
    }
}
