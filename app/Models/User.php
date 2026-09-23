<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $phone
 * @property string|null $bio
 * @property int $listings_free_count
 * @property bool $is_premium
 * @property Carbon|null $premium_until
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $address
 * @property string|null $city
 * @property string|null $postcode
 * @property string|null $country
 * @property bool $two_factor_enabled
 * @property string|null $two_factor_secret
 * @property Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property bool $is_banned
 * @property string|null $ban_reason
 * @property Carbon|null $ban_until
 * @property string $premium_plan
 * @property string|null $first_name
 * @property string|null $last_name
 * @property bool $auto_payment_enabled
 * @property int $is_public
 * @property bool $is_active
 * @property bool $is_admin
 * @property int|null $category_id
 * @property string|null $avatar
 * @property Carbon|null $terms_accepted_at
 * @property bool $can_list_when_restricted
 * @property-read Collection<int, Auction> $auctions
 * @property-read int|null $auctions_count
 * @property-read Collection<int, Bid> $bids
 * @property-read int|null $bids_count
 * @property-read Collection<int, User> $blockedUsers
 * @property-read int|null $blocked_users_count
 * @property-read Category|null $category
 * @property-read Collection<int, UserDevice> $devices
 * @property-read int|null $devices_count
 * @property-read float $reputation
 * @property-read Collection<int, Review> $givenReviews
 * @property-read int|null $given_reviews_count
 * @property-read NotificationPreference|null $notificationPreferences
 * @property-read Collection<int, Notification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, PushSubscription> $pushSubscriptions
 * @property-read int|null $push_subscriptions_count
 * @property-read Collection<int, Message> $receivedMessages
 * @property-read int|null $received_messages_count
 * @property-read Collection<int, Review> $receivedReviews
 * @property-read int|null $received_reviews_count
 * @property-read Collection<int, Report> $reportedFor
 * @property-read int|null $reported_for_count
 * @property-read Collection<int, Report> $reports
 * @property-read int|null $reports_count
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read Collection<int, Message> $sentMessages
 * @property-read int|null $sent_messages_count
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read TwoFactorAuth|null $twoFactorAuth
 * @property-read Collection<int, Watchlist> $watchlist
 * @property-read int|null $watchlist_count
 * @property-read Collection<int, Auction> $wonAuctions
 * @property-read int|null $won_auctions_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAutoPaymentEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBanReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBanUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCanListWhenRestricted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsBanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsPremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereListingsFreeCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePremiumPlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePremiumUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTermsAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'avatar',
        'phone',
        'bio',
        'listings_free_count',
        'is_premium',
        'is_public',
        'is_active',
        'is_admin',
        'can_list_when_restricted',
        'premium_until',
        'two_factor_enabled',
        'auto_payment_enabled',
        'two_factor_secret',
        'last_login_at',
        'last_login_ip',
        'is_banned',
        'ban_reason',
        'ban_until',
        'premium_plan',
        'address',
        'city',
        'postcode',
        'country',
        'category_id',
        'terms_accepted_at',
    ];

    protected $appends = ['reputation'];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'premium_until' => 'datetime',
            'ban_until' => 'datetime',
            'last_login_at' => 'datetime',
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
            'can_list_when_restricted' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'auto_payment_enabled' => 'boolean',
            'is_banned' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill(['email_verified_at' => $this->freshTimestamp()])->save();
    }

    public function auctions(): HasMany
    {
        return $this->hasMany(Auction::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function wonAuctions(): HasMany
    {
        return $this->hasMany(Auction::class, 'winner_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    /**
     * Blocked users
     */
    public function blockedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_blocks', 'blocker_id', 'blocked_user_id')
            ->withTimestamps();
    }

    /**
     * Block a user (prevents self-blocking)
     */
    public function blockUser($userId)
    {
        if ($this->id === $userId) {
            return false; // Cannot block yourself
        }

        $this->blockedUsers()->syncWithoutDetaching([$userId]);

        return true;
    }

    /**
     * Unblock a user
     */
    public function unblockUser($userId)
    {
        $this->blockedUsers()->detach($userId);

        return true;
    }

    /**
     * Recenzje wystawione
     */
    public function givenReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'from_user_id');
    }

    /**
     * Recenzje otrzymane
     */
    public function receivedReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'to_user_id');
    }

    public function reviews(): HasMany
    {
        return $this->receivedReviews();
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_by');
    }

    public function reportedFor(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    public function isPremiumActive(): bool
    {
        return $this->is_premium && $this->premium_until && $this->premium_until->isFuture();
    }

    /**
     * otrzymanych recenzji (reviews.rating), NIE osobno przechowywana
     */
    public function getReputationAttribute(): float
    {
        return round($this->receivedReviews()->avg('rating') ?? 0, 1);
    }

    public function hasFreeListing(): bool
    {
        return $this->listings_free_count > 0;
    }

    public function useFreeListing(): void
    {
        if ($this->hasFreeListing()) {
            $this->decrement('listings_free_count');
        }
    }

    // ==================== NEW RELATIONS ====================

    /**
     * 2FA Configuration
     */
    public function twoFactorAuth()
    {
        return $this->hasOne(TwoFactorAuth::class);
    }

    /**
     * User devices (sessions)
     */
    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    /**
     * Notification preferences
     */
    public function notificationPreferences()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    /**
     * User notifications
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * User watchlist
     */
    public function watchlist(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    /**
     * Ban check
     */
    public function isBanned(): bool
    {
        if (! $this->is_banned) {
            return false;
        }
        if ($this->ban_until && $this->ban_until->isPast()) {
            $this->update(['is_banned' => false]);

            return false;
        }

        return true;
    }
}
