<?php

namespace App\Observers;

use App\Events\MessageSent;
use App\Models\Message;

class MessageObserver
{
    public function created(Message $message): void
    {
        // Check if sender blocked recipient
        if ($message->sender->blockedUsers()->where('blocked_user_id', $message->recipient_id)->exists()) {
            $message->delete();

            return;
        }

        // Dispatch event for real-time delivery
        MessageSent::dispatch($message);
    }
}
