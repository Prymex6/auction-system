<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = PlatformSetting::firstOrCreate(
            ['id' => 1],
            $this->getDefaultSettings()
        );

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $settings = PlatformSetting::firstOrCreate(
            ['id' => 1],
            $this->getDefaultSettings()
        );

        $validated = $request->validate([
            'platform_name' => 'nullable|string|max:255',
            'platform_email' => 'nullable|email',
            'platform_phone' => 'nullable|string|max:20',
            'platform_description' => 'nullable|string',
            'platform_logo_url' => 'nullable|url',

            'default_auction_duration' => 'nullable|integer|min:1|max:365',
            'bid_increment_percentage' => 'nullable|integer|min:1|max:50',

            'only_admin_can_list' => 'nullable|boolean',
            'bidding_enabled' => 'nullable|boolean',
            'google_analytics_id' => ['nullable', 'string', 'max:30', 'regex:/^(G|UA|GT)-[A-Z0-9-]+$/i'],

            'require_2fa' => 'nullable|boolean',
            'require_email_verification' => 'nullable|boolean',
            'require_phone_verification' => 'nullable|boolean',
            'max_login_attempts' => 'nullable|integer|min:1',
            'lockout_duration_minutes' => 'nullable|integer|min:1',

            'max_auctions_per_day' => 'nullable|integer|min:1',
            'max_auctions_per_week' => 'nullable|integer|min:1',
            'max_auctions_per_month' => 'nullable|integer|min:1',
            'free_user_auction_limit' => 'nullable|integer|min:1',
            'premium_user_auction_limit' => 'nullable|integer|min:1',

            'auto_ban_reports_threshold' => 'nullable|integer|min:1',
            'auto_ban_duration_hours' => 'nullable|integer|min:1',
            'enable_user_reports' => 'nullable|boolean',
            'require_auction_approval' => 'nullable|boolean',

            'send_auction_ending_notification' => 'nullable|boolean',
            'send_outbid_notification' => 'nullable|boolean',
            'send_won_auction_notification' => 'nullable|boolean',
            'send_email_digest' => 'nullable|boolean',
            'email_digest_frequency' => 'nullable|in:daily,weekly,monthly',

            'system_announcement' => 'nullable|string',
            'announcement_active' => 'nullable|boolean',
            'announcement_type' => 'nullable|in:info,warning,success,danger',
            'maintenance_message' => 'nullable|string',
            'maintenance_mode' => 'nullable|boolean',

            'max_image_upload_size_mb' => 'nullable|integer|min:1',
            'max_images_per_auction' => 'nullable|integer|min:1',

            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
        ]);

        $settings->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ustawienia platformy zostały zaktualizowane',
            'data' => $settings,
        ]);
    }

    private function getDefaultSettings(): array
    {
        return [
            'platform_name' => 'Gołębiowy Lot',
            'platform_email' => config('platform.contact.email'),
            'platform_phone' => null,
            'platform_description' => 'Elitarna giełda i platforma aukcyjna dla hodowców gołębi pocztowych.',
            'platform_logo_url' => null,

            'default_auction_duration' => 7,
            'bid_increment_percentage' => 5,

            'only_admin_can_list' => false,
            'bidding_enabled' => true,
            'google_analytics_id' => null,
            'require_2fa' => false,
            'require_email_verification' => true,
            'require_phone_verification' => false,
            'max_login_attempts' => 5,
            'lockout_duration_minutes' => 15,

            'max_auctions_per_day' => 10,
            'max_auctions_per_week' => 50,
            'max_auctions_per_month' => 200,
            'free_user_auction_limit' => 5,
            'premium_user_auction_limit' => 999,

            'auto_ban_reports_threshold' => 5,
            'auto_ban_duration_hours' => 24,
            'enable_user_reports' => true,
            'require_auction_approval' => true,

            'send_auction_ending_notification' => true,
            'send_outbid_notification' => true,
            'send_won_auction_notification' => true,
            'send_email_digest' => true,
            'email_digest_frequency' => 'daily',

            'system_announcement' => null,
            'announcement_active' => false,
            'announcement_type' => 'info',
            'maintenance_message' => null,
            'maintenance_mode' => false,

            'max_image_upload_size_mb' => 10,
            'max_images_per_auction' => 10,

            'seo_title' => 'Gołębiowy Lot — aukcje gołębi pocztowych',
            'seo_description' => 'Elitarna giełda i platforma aukcyjna dla hodowców gołębi pocztowych. Sprawdzone rodowody, uczciwe aukcje, prawdziwa pasja.',
            'seo_keywords' => 'aukcje gołębi,giełda gołębi,gołębie pocztowe,hodowla,licytacja,sprzedaż',
        ];
    }

    public function reset()
    {
        $settings = PlatformSetting::first();
        if ($settings) {
            $settings->update($this->getDefaultSettings());
        } else {
            PlatformSetting::create($this->getDefaultSettings());
        }

        return response()->json([
            'success' => true,
            'message' => 'Ustawienia zostały zresetowane do domyślnych',
        ]);
    }
}
