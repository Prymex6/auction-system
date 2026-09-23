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
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->integer('free_user_auction_limit')->default(5)->after('max_auctions_per_month');
            $table->integer('premium_user_auction_limit')->default(999)->after('free_user_auction_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn(['free_user_auction_limit', 'premium_user_auction_limit']);
        });
    }
};
