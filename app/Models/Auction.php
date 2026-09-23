<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property numeric $start_price
 * @property numeric $current_price
 * @property int|null $winner_id
 * @property Carbon|null $started_at
 * @property Carbon|null $ends_at
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property bool $anti_sniper_enabled
 * @property int $sniper_threshold
 * @property int $extension_minutes
 * @property int|null $category_id
 * @property string $title
 * @property string|null $description
 * @property string $breed
 * @property int|null $year
 * @property string $gender
 * @property string|null $color
 * @property string|null $color_code
 * @property string|null $ring_number
 * @property string|null $size
 * @property array<array-key, mixed>|null $pigeon_images
 * @property array<array-key, mixed>|null $pedigree_images
 * @property string $type
 * @property string|null $rejection_reason
 * @property int|null $highest_bidder_id
 * @property-read int|null $bids_count
 * @property Carbon|null $ended_at
 * @property numeric|null $final_bid_amount
 * @property int $times_extended
 * @property-read Collection<int, Bid> $bids
 * @property-read Category|null $category
 * @property-read User|null $highestBidder
 * @property-read Collection<int, Message> $messages
 * @property-read int|null $messages_count
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read User|null $seller
 * @property-read User|null $user
 * @property-read Collection<int, Watchlist> $watchlists
 * @property-read int|null $watchlists_count
 * @property-read User|null $winner
 *
 * @method static \Database\Factories\AuctionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereAntiSniperEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereBidsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereBreed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereColorCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereCurrentPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereExtensionMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereFinalBidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereHighestBidderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction wherePedigreeImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction wherePigeonImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereRingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereSniperThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereStartPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereTimesExtended($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereWinnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereYear($value)
 *
 * @mixin \Eloquent
 */
class Auction extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Auction $auction) {
            $auction->anti_sniper_enabled = true;
        });
    }

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'breed',
        'year',
        'gender',
        'color',
        'color_code',
        'ring_number',
        'size',
        'pigeon_images',
        'pedigree_images',
        'type',
        'rejection_reason',
        'start_price',
        'current_price',
        'winner_id',
        'highest_bidder_id',
        'started_at',
        'ends_at',
        'ended_at',
        'final_bid_amount',
        'status',
        'anti_sniper_enabled',
        'sniper_threshold',
        'extension_minutes',
        'times_extended',
        'bids_count',
    ];

    protected $casts = [
        'pigeon_images' => 'array',
        'pedigree_images' => 'array',
        'start_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'final_bid_amount' => 'decimal:2',
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'ended_at' => 'datetime',
        'anti_sniper_enabled' => 'boolean',
    ];

    protected $hidden = [
        'anti_sniper_enabled',
    ];

    /**
     * Kategoria
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id')->withTrashed();
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class)->orderByDesc('created_at');
    }

    public function highestBidder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'highest_bidder_id')->withTrashed();
    }

    /**
     * Recenzje
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Watchlists
     */
    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    public function getAntiSniperEnabledAttribute($value): bool
    {
        return true;
    }

    public function isActive(): bool
    {
        if (! $this->ends_at) {
            return $this->status === 'active';
        }

        return $this->status === 'active' && $this->ends_at->isFuture();
    }

    public function isEnded(): bool
    {
        if (! $this->ends_at) {
            return $this->status === 'ended';
        }

        return $this->ends_at->isPast() || $this->status === 'ended';
    }

    public function lastBid()
    {
        return $this->bids()->latest()->first();
    }

    public function canModify(): bool
    {
        return ! $this->isActive() && $this->bids()->count() === 0;
    }

    /**
     * Check if auction should be extended (anti-sniper)
     */
    public function shouldExtendForSniper(): bool
    {
        if (! $this->anti_sniper_enabled || ! $this->ends_at) {
            return false;
        }

        $minutesLeft = now()->diffInMinutes($this->ends_at, false);

        return $minutesLeft <= $this->sniper_threshold && $minutesLeft > 0;
    }

    /**
     * Extend auction by N minutes
     */
    public function extendForSniper(): void
    {
        if (! $this->ends_at) {
            return;
        }

        $this->update([
            'ends_at' => $this->ends_at->addMinutes($this->extension_minutes),
            'times_extended' => $this->times_extended + 1,
        ]);
    }

    /**
     * Payments
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
