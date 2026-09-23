<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use Illuminate\Http\Request;

class ErrorLogController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 50);
        $onlyUnresolved = $request->boolean('unresolved_only', false);

        $query = ErrorLog::query();

        if ($onlyUnresolved) {
            $query->where('resolved', false);
        }

        $logs = $query->orderByDesc('last_seen_at')->paginate($limit);

        return response()->json([
            'data' => $logs,
        ]);
    }

    public function show(ErrorLog $errorLog)
    {
        return response()->json([
            'data' => $errorLog,
        ]);
    }

    public function resolve(ErrorLog $errorLog)
    {
        $errorLog->update(['resolved' => true]);

        return response()->json([
            'message' => 'Błąd oznaczony jako rozwiązany',
            'data' => $errorLog,
        ]);
    }

    public function destroy(ErrorLog $errorLog)
    {
        $errorLog->delete();

        return response()->json([
            'message' => 'Wpis usunięty',
        ]);
    }
}
