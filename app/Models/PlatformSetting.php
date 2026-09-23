<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $platform_name
 * @property string $platform_email
 * @property string|null $platform_phone
 * @property string|null $platform_description
 * @property string|null $platform_logo_url
 * @property int $default_auction_duration
 * @property int $bid_increment_percentage
 * @property bool $require_2fa
 * @property bool $require_email_verification
 * @property bool $require_phone_verification
 * @property int $max_login_attempts
 * @property int $lockout_duration_minutes
 * @property int $max_auctions_per_day
 * @property int $max_auctions_per_week
 * @property int $max_auctions_per_month
 * @property int $auto_ban_reports_threshold
 * @property int $auto_ban_duration_hours
 * @property bool $enable_user_reports
 * @property bool $require_auction_approval
 * @property bool $send_auction_ending_notification
 * @property bool $send_outbid_notification
 * @property bool $send_won_auction_notification
 * @property bool $send_email_digest
 * @property string $email_digest_frequency
 * @property string|null $system_announcement
 * @property bool $announcement_active
 * @property string $announcement_type
 * @property string|null $maintenance_message
 * @property bool $maintenance_mode
 * @property int $max_image_upload_size_mb
 * @property int $max_images_per_auction
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $seo_keywords
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $free_user_auction_limit
 * @property int $premium_user_auction_limit
 * @property bool $only_admin_can_list
 * @property bool $bidding_enabled
 * @property string|null $google_analytics_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereAnnouncementActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereAnnouncementType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereAutoBanDurationHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereAutoBanReportsThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereBidIncrementPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereBiddingEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereDefaultAuctionDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereEmailDigestFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereEnableUserReports($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereFreeUserAuctionLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereGoogleAnalyticsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereLockoutDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereMaintenanceMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereMaintenanceMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereMaxAuctionsPerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereMaxAuctionsPerMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereMaxAuctionsPerWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereMaxImageUploadSizeMb($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereMaxImagesPerAuction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereMaxLoginAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereOnlyAdminCanList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting wherePlatformDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting wherePlatformEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting wherePlatformLogoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting wherePlatformName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting wherePlatformPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting wherePremiumUserAuctionLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereRequire2fa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereRequireAuctionApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereRequireEmailVerification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereRequirePhoneVerification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereSendAuctionEndingNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereSendEmailDigest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereSendOutbidNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereSendWonAuctionNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereSeoDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereSeoKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereSeoTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereSystemAnnouncement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PlatformSetting extends Model
{
    protected $fillable = [
        'platform_name',
        'platform_email',
        'platform_phone',
        'platform_description',
        'platform_logo_url',

        'default_auction_duration',
        'bid_increment_percentage',

        'require_2fa',
        'require_email_verification',
        'require_phone_verification',
        'max_login_attempts',
        'lockout_duration_minutes',

        'max_auctions_per_day',
        'max_auctions_per_week',
        'max_auctions_per_month',
        'free_user_auction_limit',
        'premium_user_auction_limit',

        'auto_ban_reports_threshold',
        'auto_ban_duration_hours',
        'enable_user_reports',
        'require_auction_approval',

        'send_auction_ending_notification',
        'send_outbid_notification',
        'send_won_auction_notification',
        'send_email_digest',
        'email_digest_frequency',

        'system_announcement',
        'announcement_active',
        'announcement_type',
        'maintenance_message',
        'maintenance_mode',

        'max_image_upload_size_mb',
        'max_images_per_auction',

        'seo_title',
        'seo_description',
        'seo_keywords',

        'only_admin_can_list',
        'bidding_enabled',
        'google_analytics_id',
    ];

    protected $casts = [
        'only_admin_can_list' => 'boolean',
        'bidding_enabled' => 'boolean',
        'require_2fa' => 'boolean',
        'require_email_verification' => 'boolean',
        'require_phone_verification' => 'boolean',
        'enable_user_reports' => 'boolean',
        'require_auction_approval' => 'boolean',
        'send_auction_ending_notification' => 'boolean',
        'send_outbid_notification' => 'boolean',
        'send_won_auction_notification' => 'boolean',
        'send_email_digest' => 'boolean',
        'announcement_active' => 'boolean',
        'maintenance_mode' => 'boolean',
    ];
}
