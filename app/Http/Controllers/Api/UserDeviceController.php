<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\Request;

class UserDeviceController extends Controller
{
    public function index(Request $request)
    {
        $currentTokenId = (string) $request->user()->currentAccessToken()?->id;

        $devices = $request->user()
            ->devices()
            ->orderByDesc('last_activity_at')
            ->get()
            ->map(function ($device) use ($currentTokenId) {
                return [
                    'id' => $device->id,
                    'device_name' => $device->device_name,
                    'device_type' => $device->device_type,
                    'ip_address' => $device->ip_address,
                    'last_activity_at' => $device->last_activity_at?->diffForHumans(),
                    'is_current' => $device->device_token === $currentTokenId,
                    'created_at' => $device->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $devices,
        ]);
    }

    public function destroy(Request $request, UserDevice $device)
    {
        if ($device->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Urządzenie zostało wylogowane',
        ]);
    }

    public function destroyOthers(Request $request)
    {
        // DeviceService::registerOrUpdate), NIE surowy bearer token - wczesniej
        // urzadzenia, wlacznie z biezacym.
        $currentTokenId = (string) $request->user()->currentAccessToken()?->id;

        $deleted = $request->user()
            ->devices()
            ->where('device_token', '!=', $currentTokenId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Wylogowano {$deleted} urządzeń",
            'count' => $deleted,
        ]);
    }
}
