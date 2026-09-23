<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Models\User;

class NotificationRepository
{
    public function create(array $data): Notification
    {
        return Notification::create($data);
    }

    public function getById(int $id): ?Notification
    {
        return Notification::find($id);
    }

    public function getByUser(User $user, int $perPage = 20)
    {
        return Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getUnreadByUser(User $user, int $perPage = 20)
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function markAsRead(Notification $notification): bool
    {
        return $notification->update(['read_at' => now()]);
    }

    public function markAllAsRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function update(Notification $notification, array $data): bool
    {
        return $notification->update($data);
    }

    public function delete(Notification $notification): bool
    {
        return $notification->delete();
    }

    public function deleteOld(int $daysOld = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($daysOld))->delete();
    }

    public function getByType(User $user, string $type, int $perPage = 20)
    {
        return Notification::where('user_id', $user->id)
            ->where('type', $type)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
