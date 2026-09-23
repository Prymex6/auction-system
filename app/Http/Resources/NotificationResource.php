<?php

namespace App\Http\Resources;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Notification
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'read' => $this->read,
            'read_at' => $this->read_at?->toIso8601String(),
            'auction_id' => $this->auction_id,
            'related_user' => [
                'id' => $this->relatedUser?->id,
                'name' => $this->relatedUser?->name,
            ],
            'data' => $this->data,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
