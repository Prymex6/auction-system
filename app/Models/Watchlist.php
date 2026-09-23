<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Watchlist - Favorite auctions
 *
 * @property int $id
 * @property int $user_id
 * @property int $auction_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Auction $auction
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist whereAuctionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Watchlist extends Model
{
    protected $fillable = [
        'user_id',
        'auction_id',
    ];

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Auction relationship
     */
    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }
}
