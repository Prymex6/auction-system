<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function create(User $user): bool
    {
        return ! $user->is_banned;
    }

    public function update(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id && $message->created_at->diffInHours(now()) < 1;
    }

    public function delete(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id || $user->id === $message->recipient_id;
    }

    public function view(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id || $user->id === $message->recipient_id || $user->is_admin;
    }
}
