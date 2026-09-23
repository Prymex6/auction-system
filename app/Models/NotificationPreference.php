<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property bool $email_notifications
 * @property bool $sound_notifications
 * @property bool $push_notifications
 * @property bool $bid_notifications
 * @property bool $message_notifications
 * @property bool $auction_end_notifications
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereAuctionEndNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereBidNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereEmailNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereMessageNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference wherePushNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereSoundNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereUserId($value)
 *
 * @mixin \Eloquent
 */
class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_notifications',
        'sound_notifications',
        'push_notifications',
        'bid_notifications',
        'message_notifications',
        'auction_end_notifications',
    ];

    protected function casts(): array
    {
        return [
            'email_notifications' => 'boolean',
            'sound_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'bid_notifications' => 'boolean',
            'message_notifications' => 'boolean',
            'auction_end_notifications' => 'boolean',
        ];
    }

    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
