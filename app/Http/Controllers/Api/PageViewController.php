<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use Illuminate\Http\Request;

class PageViewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'path' => 'required|string|max:255',
        ]);

        $path = $request->input('path');

        if (str_starts_with($path, '/admin')) {
            return response()->noContent();
        }

        $visitorHash = hash('sha256', $request->ip().'|'.$request->userAgent().'|'.now()->toDateString());

        PageView::create([
            'path' => $path,
            'visitor_hash' => $visitorHash,
            'viewed_at' => now(),
        ]);

        return response()->noContent();
    }
}
