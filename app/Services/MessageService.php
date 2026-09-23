<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Pagination\Paginator;

class MessageService
{
    public function getConversations(User $user, int $perPage = 15): Paginator
    {
        return Message::where('sender_id', $user->id)
            ->orWhere('recipient_id', $user->id)
            ->selectRaw('CASE WHEN sender_id = ? THEN recipient_id ELSE sender_id END as other_user_id', [$user->id])
            ->distinct()
            ->paginate($perPage);
    }

    public function getConversationWith(User $currentUser, User $otherUser): Paginator
    {
        return Message::where(function ($query) use ($currentUser, $otherUser) {
            $query->where('sender_id', $currentUser->id)->where('recipient_id', $otherUser->id)
                ->orWhere('sender_id', $otherUser->id)->where('recipient_id', $currentUser->id);
        })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function markAsRead(Message $message, User $user): bool
    {
        if ($message->recipient_id !== $user->id) {
            return false;
        }

        return $message->update(['read_at' => now()]);
    }

    public function sendMessage(User $sender, User $recipient, string $body, ?int $auctionId = null): Message
    {
        return Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'content' => $body,
            'auction_id' => $auctionId,
            'read_at' => null,
        ]);
    }

    public function deleteMessage(Message $message, User $user): bool
    {
        if ($message->sender_id !== $user->id && $message->recipient_id !== $user->id) {
            return false;
        }

        return $message->delete();
    }

    public function getUnreadCount(User $user): int
    {
        return Message::where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function blockUser(User $blocker, User $blocked): void
    {
        $blocker->blockedUsers()->attach($blocked->id);
    }

    public function unblockUser(User $blocker, User $blocked): void
    {
        $blocker->blockedUsers()->detach($blocked->id);
    }

    public function isBlocked(User $sender, User $recipient): bool
    {
        return $recipient->blockedUsers()
            ->where('blocked_user_id', $sender->id)
            ->exists();
    }
}
