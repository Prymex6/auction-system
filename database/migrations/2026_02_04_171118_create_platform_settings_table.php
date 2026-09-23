<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();

            $table->string('platform_name')->default('Pigeon Auction');
            $table->string('platform_email')->default('info@pigeon-auction.local');
            $table->string('platform_phone')->nullable();
            $table->text('platform_description')->nullable();
            $table->string('platform_logo_url')->nullable();

            $table->integer('default_auction_duration')->default(7); // Dni
            $table->integer('max_auction_duration')->default(30); // Dni
            $table->integer('bid_increment_percentage')->default(5); // Procent wzrostu

            $table->boolean('require_2fa')->default(false);
            $table->boolean('require_email_verification')->default(true);
            $table->boolean('require_phone_verification')->default(false);
            $table->integer('max_login_attempts')->default(5);
            $table->integer('lockout_duration_minutes')->default(15);

            $table->integer('max_auctions_per_day')->default(10);
            $table->integer('max_auctions_per_week')->default(50);
            $table->integer('max_auctions_per_month')->default(200);

            $table->integer('auto_ban_reports_threshold')->default(5); // Ilość zgłoszeń do automatycznego bana
            $table->integer('auto_ban_duration_hours')->default(24);
            $table->boolean('enable_user_reports')->default(true);
            $table->boolean('enable_auction_moderation')->default(true);
            $table->boolean('require_auction_approval')->default(true);

            $table->boolean('send_auction_ending_notification')->default(true);
            $table->boolean('send_outbid_notification')->default(true);
            $table->boolean('send_won_auction_notification')->default(true);
            $table->boolean('send_email_digest')->default(true);
            $table->string('email_digest_frequency')->default('daily'); // daily, weekly, monthly

            $table->text('system_announcement')->nullable();
            $table->boolean('announcement_active')->default(false);
            $table->string('announcement_type')->default('info'); // info, warning, success, danger
            $table->text('maintenance_message')->nullable();
            $table->boolean('maintenance_mode')->default(false);

            $table->integer('max_image_upload_size_mb')->default(10);
            $table->integer('max_images_per_auction')->default(10);

            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('seo_keywords')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
