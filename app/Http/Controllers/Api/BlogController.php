<?php

namespace App\Http\Controllers\Api;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 10);

        $posts = BlogPost::query()
            ->published()
            ->recent()
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Posts retrieved successfully',
            'data' => $posts->items(),
            'pagination' => [
                'total' => $posts->total(),
                'per_page' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
            ],
        ]);
    }

    public function show(BlogPost $blogPost)
    {
        if (! $blogPost->published || $blogPost->published_at > now()) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $blogPost->load('author:id,name,email');

        return response()->json([
            'success' => true,
            'message' => 'Post retrieved successfully',
            'data' => $blogPost,
        ]);
    }

    public function store(Request $request)
    {
        // Sprawdzenie autoryzacji - admin only
        $user = $request->user();
        if (! $user || ! $user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:blog_posts',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|string',
            'published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $validated['author_id'] = $user->id;
        $validated['slug'] = Str::slug($validated['title']);

        if (($validated['published'] ?? false) && empty($validated['published_at'] ?? null)) {
            $validated['published_at'] = now();
        }

        $post = BlogPost::create($validated);
        $post->load('author:id,name,email');

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => $post,
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        // Sprawdzenie autoryzacji - admin only
        $user = $request->user();
        if (! $user || ! $user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255|unique:blog_posts,title,'.$blogPost->id,
            'content' => 'sometimes|string',
            'excerpt' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|string',
            'published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        if (($validated['published'] ?? false) && empty($validated['published_at'] ?? null)) {
            $validated['published_at'] = now();
        }

        $blogPost->update($validated);
        $blogPost->load('author:id,name,email');

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => $blogPost,
        ]);
    }

    public function destroy(BlogPost $blogPost)
    {
        // Sprawdzenie autoryzacji - admin only
        $user = request()->user();
        if (! $user || ! $user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        $blogPost->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }
}
