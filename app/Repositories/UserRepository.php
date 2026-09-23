<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function getById(int $id): ?User
    {
        return User::with(['auctions', 'bids', 'reviews'])->find($id);
    }

    public function getByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function getActive(int $perPage = 15)
    {
        return User::where('is_banned', false)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getBanned(int $perPage = 15)
    {
        return User::where('is_banned', true)
            ->orderByDesc('banned_at')
            ->paginate($perPage);
    }

    public function getPremium(int $perPage = 15)
    {
        return User::where('is_premium', true)
            ->where('premium_until', '>', now())
            ->orderByDesc('premium_until')
            ->paginate($perPage);
    }

    public function getExpiredPremium()
    {
        return User::where('is_premium', true)
            ->where('premium_until', '<', now())
            ->get();
    }

    public function getTotalCount(): int
    {
        return User::count();
    }

    public function getActiveCount(): int
    {
        return User::where('is_banned', false)->count();
    }

    public function getBannedCount(): int
    {
        return User::where('is_banned', true)->count();
    }

    public function getPremiumCount(): int
    {
        return User::where('is_premium', true)
            ->where('premium_until', '>', now())
            ->count();
    }

    public function search(string $query, int $perPage = 15)
    {
        return User::where('name', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%")
            ->where('is_banned', false)
            ->paginate($perPage);
    }
}
