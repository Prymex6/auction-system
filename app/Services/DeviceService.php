<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use Jenssegers\Agent\Agent;

class DeviceService
{
    /**
     * pokazywalo faktycznie uzywane urzadzenie, a nie to z ostatniego logowania.
     */
    public static function registerOrUpdate(User $user, ?string $userAgent, string $ipAddress, int|string $currentTokenId): UserDevice
    {
        $agent = new Agent;
        $agent->setUserAgent($userAgent ?? 'Unknown');

        $deviceName = $agent->browser().' on '.$agent->platform();
        $deviceType = self::getDeviceType($agent);

        $user->devices()->update(['is_current' => false]);

        $device = $user->devices()
            ->where('ip_address', $ipAddress)
            ->where('device_name', $deviceName)
            ->first();

        if ($device) {
            $device->update([
                'last_activity_at' => now(),
                'is_current' => true,
                'device_token' => (string) $currentTokenId,
            ]);
        } else {
            $device = $user->devices()->create([
                'device_token' => (string) $currentTokenId,
                'device_name' => $deviceName,
                'device_type' => $deviceType,
                'ip_address' => $ipAddress,
                'last_activity_at' => now(),
                'is_current' => true,
            ]);
        }

        return $device;
    }

    private static function getDeviceType(Agent $agent): string
    {
        if ($agent->isMobile()) {
            return 'mobile';
        } elseif ($agent->isTablet()) {
            return 'tablet';
        } else {
            return 'web';
        }
    }

    public static function updateActivity(User $user, string $userAgent, string $ipAddress): void
    {
        $agent = new Agent;
        $agent->setUserAgent($userAgent);

        $deviceName = $agent->browser().' on '.$agent->platform();

        $user->devices()
            ->where('ip_address', $ipAddress)
            ->where('device_name', $deviceName)
            ->update([
                'last_activity_at' => now(),
            ]);
    }
}
