<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadFlowTest extends TestCase
{
    public function test_full_upload_info_and_delete_flow()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('flow.jpg', 1200, 800)->size(800);

        // Upload
        $uploadResponse = $this->actingAs($user, 'sanctum')->post('/api/images/upload', [
            'image' => $file,
            'type' => 'auction',
        ]);

        $uploadResponse->assertStatus(201);
        $uploadData = $uploadResponse->json();
        $this->assertTrue($uploadData['success']);
        $this->assertArrayHasKey('path', $uploadData);

        $path = $uploadData['path'];

        // Info
        $infoResponse = $this->actingAs($user, 'sanctum')->get('/api/images/'.urlencode($path).'/info');
        $infoResponse->assertStatus(200);
        $infoData = $infoResponse->json();
        $this->assertTrue($infoData['success']);
        $this->assertEquals($path, $infoData['path']);

        // Delete (Controller expects POST {path}/delete with 'path' in body in delete method validation)
        $deleteResponse = $this->actingAs($user, 'sanctum')->post('/api/images/'.urlencode($path).'/delete', [
            'path' => $path,
        ]);

        $deleteResponse->assertStatus(200);
        $this->assertTrue($deleteResponse->json('success'));

        // Ensure file is deleted from fake storage
        $this->assertFalse(Storage::disk('public')->exists($path), "File {$path} should be deleted");
    }
}
