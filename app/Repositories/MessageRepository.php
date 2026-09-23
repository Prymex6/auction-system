<?php

namespace App\Repositories;

use App\Models\Message;
use App\Models\User;
use Illuminate\Pagination\Paginator;

class MessageRepository
{
    public function getConversations(User $user, int $perPage = 15): Paginator
    {
        return Message::query()
            ->where('sender_id', $user->id)
            ->orWhere('recipient_id', $user->id)
            ->with(['sender', 'recipient'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getConversationWith(User $user, User $other, int $perPage = 20): Paginator
    {
        return Message::query()
            ->where(function ($query) use ($user, $other) {
                $query->where('sender_id', $user->id)->where('recipient_id', $other->id)
                    ->orWhere('sender_id', $other->id)->where('recipient_id', $user->id);
            })
            ->with(['sender', 'recipient'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(array $data): Message
    {
        return Message::create($data);
    }

    public function getById(int $id): ?Message
    {
        return Message::with(['sender', 'recipient'])->find($id);
    }

    public function update(Message $message, array $data): bool
    {
        return $message->update($data);
    }

    public function delete(Message $message): bool
    {
        return $message->delete();
    }

    public function getUnreadCount(User $user): int
    {
        return Message::where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function getUnreadMessages(User $user): array
    {
        return Message::where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->with(['sender'])
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }
}
