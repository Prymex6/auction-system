<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function conversations(Request $request)
    {
        $userId = $request->user()->id;

        $messages = Message::query()
            ->where('sender_id', $userId)
            ->orWhere('recipient_id', $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get(['id', 'sender_id', 'recipient_id', 'content', 'read_at', 'created_at']);

        $otherIds = $messages
            ->map(fn ($m) => $m->sender_id === $userId ? $m->recipient_id : $m->sender_id)
            ->unique();
        $others = User::whereIn('id', $otherIds)->get()->keyBy('id');

        $conversations = $messages
            ->groupBy(fn ($m) => $m->sender_id === $userId ? $m->recipient_id : $m->sender_id)
            ->map(function ($msgs, $otherId) use ($userId, $others) {
                $other = $others->get($otherId);
                if (! $other) {
                    return null;
                }
                $last = $msgs->first();

                return [
                    'id' => (int) $otherId,
                    'user_id' => (int) $otherId,
                    'user_name' => $other->name,
                    'user_avatar' => $other->avatar ?? null,
                    'last_message' => Str::limit($last->content, 80),
                    'last_message_at' => $last->created_at,
                    'last_message_id' => $last->id,
                    'unread_count' => $msgs
                        ->filter(fn ($m) => $m->recipient_id === $userId && $m->read_at === null)
                        ->count(),
                ];
            })
            ->filter()
            ->sortByDesc('last_message_at')
            ->values();

        return response()->json([
            'data' => $conversations,
        ]);
    }

    public function show(User $user, Request $request)
    {
        $me = $request->user()->id;

        $messages = Message::query()
            ->where(function ($q) use ($user, $me) {
                $q->where('sender_id', $me)
                    ->where('recipient_id', $user->id);
            })
            ->orWhere(function ($q) use ($user, $me) {
                $q->where('sender_id', $user->id)
                    ->where('recipient_id', $me);
            })
            ->orderBy('created_at') // chronologicznie — jak w czacie
            ->orderBy('id')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'content' => $m->content,
                'sender_id' => $m->sender_id,
                'recipient_id' => $m->recipient_id,
                'created_at' => $m->created_at,
                'is_sent' => $m->sender_id === $me,
                'is_read' => $m->read_at !== null,
            ]);

        // Mark as read
        Message::where('sender_id', $user->id)
            ->where('recipient_id', $me)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'data' => $messages,
        ]);
    }

    public function store(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'Nie możesz wysłać wiadomości samemu sobie',
            ], 422);
        }

        // Check if sender is banned. Uwaga: isBanned() (nie sam ban_until) -
        if ($request->user()->isBanned()) {
            return response()->json([
                'message' => 'Twoje konto jest zablokowane — nie możesz wysyłać wiadomości',
            ], 403);
        }

        // Check if sender is blocked by recipient
        if ($user->blockedUsers()->where('blocked_user_id', $request->user()->id)->exists()) {
            return response()->json([
                'message' => 'Nie możesz wysyłać wiadomości do tego użytkownika',
            ], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'auction_id' => 'nullable|exists:auctions,id',
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'recipient_id' => $user->id,
            'content' => $validated['content'],
            'auction_id' => $validated['auction_id'] ?? null,
        ]);

        app(PushNotificationService::class)->sendToUser(
            $user,
            'Nowa wiadomość od '.$request->user()->name,
            Str::limit($validated['content'], 100),
            '/messages'
        );

        return response()->json($message, 201);
    }

    public function markAsRead(Message $message, Request $request)
    {
        if ($message->recipient_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $message->update(['read_at' => now()]);

        return response()->json(['message' => 'Oznaczone']);
    }

    public function destroy(Message $message, Request $request)
    {
        if ($message->sender_id !== $request->user()->id && $message->recipient_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Wiadomość usunięta']);
    }

    public function unreadCount(Request $request)
    {
        $count = Message::where('recipient_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }
}
