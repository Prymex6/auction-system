<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\ImageUpload;
use App\Models\PlatformSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;

/**
 * Image Upload Controller
 * Handles image uploads, compression, and storage
 */
class ImageController extends Controller
{
    /**
     * Upload and store image
     *
     * @return JsonResponse
     */
    public function upload(Request $request)
    {
        try {
            $maxSizeMb = PlatformSetting::first()?->max_image_upload_size_mb ?? 15;

            $validated = $request->validate([
                'image' => [
                    'required',
                    'image',
                    'max:'.($maxSizeMb * 1024),
                    'mimes:jpeg,png,webp,gif',
                    'dimensions:min_width=200,min_height=200',
                ],
                'type' => 'required|string|in:auction,profile,gallery',
            ]);

            // Initialize image manager
            $manager = ImageManager::gd();

            // Read uploaded file
            $uploadedImage = $request->file('image');
            $image = $manager->read($uploadedImage->getRealPath());

            $encoded = $image
                ->scaleDown(1600, 1200)
                ->encodeByMediaType('image/jpeg', quality: 80);

            // Generate filename
            $filename = sprintf(
                'images/%s/%s_%d.jpg',
                $validated['type'],
                uniqid(),
                now()->timestamp
            );

            // Store image
            Storage::disk('public')->put($filename, (string) $encoded);

            // Zapamietaj wlasciciela - potrzebne do weryfikacji uprawnien w delete()/info(),
            ImageUpload::create([
                'path' => $filename,
                'user_id' => $request->user()->id,
            ]);

            // Get image dimensions after processing
            $image = $manager->read(Storage::disk('public')->path($filename));
            $dimensions = "{$image->width()}x{$image->height()}";

            // Get file size
            $size = Storage::disk('public')->size($filename);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'url' => Storage::url($filename),
                'path' => $filename,
                'size' => $size,
                'dimensions' => $dimensions,
                'uploadedAt' => now()->toIso8601String(),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Image upload failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete image
     *
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        try {
            $validated = $request->validate([
                'path' => ['required', 'string', 'regex:#^images/(auction|profile|gallery)/[A-Za-z0-9_\-.]+\.(jpg|jpeg|png|webp)$#'],
            ]);

            if (! $this->userOwnsImage($request->user(), $validated['path'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brak uprawnień do tego pliku',
                ], 403);
            }

            // Delete from storage
            if (Storage::disk('public')->exists($validated['path'])) {
                Storage::disk('public')->delete($validated['path']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image',
            ], 500);
        }
    }

    /**
     * Get image info
     *
     * @param  string  $path
     * @return JsonResponse
     */
    public function info(Request $request, $path)
    {
        try {
            if (! preg_match('#^images/(auction|profile|gallery)/[A-Za-z0-9_\-.]+\.(jpg|jpeg|png|webp)$#', $path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image path',
                ], 422);
            }

            if (! $this->userOwnsImage($request->user(), $path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brak uprawnień do tego pliku',
                ], 403);
            }

            $fullPath = Storage::disk('public')->path($path);

            if (! Storage::disk('public')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found',
                ], 404);
            }

            $manager = ImageManager::gd();
            $image = $manager->read($fullPath);
            $size = Storage::disk('public')->size($path);

            return response()->json([
                'success' => true,
                'url' => Storage::url($path),
                'path' => $path,
                'size' => $size,
                'dimensions' => "{$image->width()}x{$image->height()}",
                'uploadedAt' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get image info',
            ], 500);
        }
    }

    private function userOwnsImage($user, string $path): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (str_starts_with($path, 'images/profile/') && $user->avatar === $path) {
            return true;
        }

        if (str_starts_with($path, 'images/auction/')) {
            $attachedToOwnAuction = Auction::where('user_id', $user->id)
                ->where(function ($query) use ($path) {
                    $query->whereJsonContains('pigeon_images', $path)
                        ->orWhereJsonContains('pedigree_images', $path);
                })
                ->exists();

            if ($attachedToOwnAuction) {
                return true;
            }
        }

        return ImageUpload::where('path', $path)
            ->where('user_id', $user->id)
            ->exists();
    }
}
