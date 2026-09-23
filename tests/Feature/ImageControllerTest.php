<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_image_stores_file_and_returns_metadata()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('photo.jpg', 800, 600)->size(500);

        $response = $this->actingAs($user, 'sanctum')->post('/api/images/upload', [
            'image' => $file,
            'type' => 'auction',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['success', 'message', 'url', 'path', 'size', 'dimensions', 'uploadedAt']);

        $path = $response->json('path');
        $this->assertTrue(Storage::disk('public')->exists($path), "File {$path} does not exist");
    }

    public function test_upload_accepts_gif_as_advertised_by_the_upload_field_hint()
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('photo.gif', 800, 600)->size(500);

        $response = $this->actingAs($user, 'sanctum')->post('/api/images/upload', [
            'image' => $file,
            'type' => 'auction',
        ]);

        $response->assertStatus(201);
    }

    public function test_upload_rejects_non_image_file_disguised_with_image_extension()
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->createWithContent('nie-zdjecie.jpg', 'to jest zwykly plik tekstowy, nie obrazek');

        $response = $this->actingAs($user, 'sanctum')->post('/api/images/upload', [
            'image' => $file,
            'type' => 'auction',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('image');
    }

    public function test_upload_rejects_pdf_file()
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('rodowod.pdf', 200, 'application/pdf');

        $response = $this->actingAs($user, 'sanctum')->post('/api/images/upload', [
            'image' => $file,
            'type' => 'auction',
        ]);

        $response->assertStatus(422);
    }

    public function test_upload_rejects_image_larger_than_configured_setting()
    {
        Storage::fake('public');
        PlatformSetting::updateOrCreate([], ['max_image_upload_size_mb' => 2]);
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('too-big.jpg', 800, 600)->size(3072);

        $response = $this->actingAs($user, 'sanctum')->post('/api/images/upload', [
            'image' => $file,
            'type' => 'auction',
        ]);

        $response->assertStatus(422);
    }

    public function test_upload_accepts_image_within_configured_setting()
    {
        Storage::fake('public');
        PlatformSetting::updateOrCreate([], ['max_image_upload_size_mb' => 2]);
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('ok.jpg', 800, 600)->size(1024);

        $response = $this->actingAs($user, 'sanctum')->post('/api/images/upload', [
            'image' => $file,
            'type' => 'auction',
        ]);

        $response->assertStatus(201);
    }

    public function test_user_cannot_delete_another_users_auction_image()
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $path = 'images/auction/victim_123.jpg';
        Storage::disk('public')->put($path, 'fake-content');
        Auction::factory()->for($owner)->create(['pigeon_images' => [$path]]);

        $response = $this->actingAs($stranger, 'sanctum')->post("/api/images/{$path}/delete", [
            'path' => $path,
        ]);

        $response->assertStatus(403);
        $this->assertTrue(Storage::disk('public')->exists($path));
    }

    public function test_user_can_delete_own_auction_image()
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $path = 'images/auction/mine_123.jpg';
        Storage::disk('public')->put($path, 'fake-content');
        Auction::factory()->for($owner)->create(['pigeon_images' => [$path]]);

        $response = $this->actingAs($owner, 'sanctum')->post("/api/images/{$path}/delete", [
            'path' => $path,
        ]);

        $response->assertStatus(200);
        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_admin_can_delete_any_auction_image()
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $path = 'images/auction/admin_target_123.jpg';
        Storage::disk('public')->put($path, 'fake-content');
        Auction::factory()->for($owner)->create(['pigeon_images' => [$path]]);

        $response = $this->actingAs($admin, 'sanctum')->post("/api/images/{$path}/delete", [
            'path' => $path,
        ]);

        $response->assertStatus(200);
        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_user_cannot_read_info_of_another_users_profile_image()
    {
        Storage::fake('public');
        $owner = User::factory()->create(['avatar' => 'images/profile/victim_avatar.jpg']);
        $stranger = User::factory()->create();
        Storage::disk('public')->put('images/profile/victim_avatar.jpg', 'fake-content');

        $response = $this->actingAs($stranger, 'sanctum')->get('/api/images/images/profile/victim_avatar.jpg/info');

        $response->assertStatus(403);
    }
}
