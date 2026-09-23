<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $auction_id
 * @property int $user_id
 * @property numeric $amount
 * @property bool $is_auto_bid
 * @property numeric|null $max_auto_bid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property bool $is_winning_bid
 * @property-read Auction $auction
 * @property-read Payment|null $payment
 * @property-read User|null $user
 *
 * @method static \Database\Factories\BidFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid whereAuctionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid whereIsAutoBid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid whereIsWinningBid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid whereMaxAutoBid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Bid extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'auction_id',
        'user_id',
        'amount',
        'is_auto_bid',
        'max_auto_bid',
        'is_winning_bid',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'max_auto_bid' => 'decimal:2',
        'is_auto_bid' => 'boolean',
        'is_winning_bid' => 'boolean',
    ];

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Payment
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
