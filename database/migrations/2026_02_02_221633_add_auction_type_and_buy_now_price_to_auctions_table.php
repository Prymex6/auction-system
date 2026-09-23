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
        Schema::table('auctions', function (Blueprint $table) {
            if (! Schema::hasColumn('auctions', 'auction_type')) {
                $table->enum('auction_type', ['auction', 'buy_now', 'both'])->default('auction')->after('status');
            }
            if (! Schema::hasColumn('auctions', 'buy_now_price')) {
                $table->decimal('buy_now_price', 10, 2)->nullable()->after('reserve_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            if (Schema::hasColumn('auctions', 'auction_type')) {
                $table->dropColumn('auction_type');
            }
            if (Schema::hasColumn('auctions', 'buy_now_price')) {
                $table->dropColumn('buy_now_price');
            }
        });
    }
};
