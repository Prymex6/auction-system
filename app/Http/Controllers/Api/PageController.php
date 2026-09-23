<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LegalPage;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => LegalPage::orderBy('id')->get(['slug', 'title', 'updated_at']),
        ]);
    }

    public function show(string $slug)
    {
        $page = LegalPage::where('slug', $slug)->first();

        if (! $page) {
            return response()->json(['message' => 'Strona nie istnieje'], 404);
        }

        return response()->json(['data' => $page]);
    }

    public function update(Request $request, string $slug)
    {
        $page = LegalPage::where('slug', $slug)->first();

        if (! $page) {
            return response()->json(['message' => 'Strona nie istnieje'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string|max:100000',
        ]);

        $page->update($validated);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'updated',
            'model_type' => 'LegalPage',
            'model_id' => $page->id,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json([
            'message' => 'Strona została zaktualizowana',
            'data' => $page->fresh(),
        ]);
    }
}
