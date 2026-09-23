<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Email notifications
            $table->boolean('email_on_bid')->default(true);
            $table->boolean('email_on_outbid')->default(true);
            $table->boolean('email_on_auction_won')->default(true);
            $table->boolean('email_on_message')->default(true);
            $table->boolean('email_on_listing_status')->default(true);

            // Push notifications
            $table->boolean('push_on_bid')->default(true);
            $table->boolean('push_on_outbid')->default(true);
            $table->boolean('push_on_auction_won')->default(true);
            $table->boolean('push_on_message')->default(true);

            // Frequency
            $table->enum('digest_frequency', ['instant', 'daily', 'weekly', 'never'])->default('instant');
            $table->boolean('disable_all_notifications')->default(false);

            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
