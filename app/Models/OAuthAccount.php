<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthAccount query()
 *
 * @mixin \Eloquent
 */
class OAuthAccount extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'email',
        'name',
        'avatar_url',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
