<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $fingerprint
 * @property string $exception_class
 * @property string $message
 * @property string|null $file
 * @property int|null $line
 * @property string|null $trace
 * @property string|null $url
 * @property string|null $method
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property int $occurrences
 * @property Carbon $first_seen_at
 * @property Carbon $last_seen_at
 * @property bool $resolved
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereExceptionClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereFingerprint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereFirstSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereLastSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereOccurrences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereResolved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereTrace($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ErrorLog whereUserId($value)
 *
 * @mixin \Eloquent
 */
class ErrorLog extends Model
{
    protected $fillable = [
        'fingerprint',
        'exception_class',
        'message',
        'file',
        'line',
        'trace',
        'url',
        'method',
        'user_id',
        'ip_address',
        'occurrences',
        'first_seen_at',
        'last_seen_at',
        'resolved',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'resolved' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
