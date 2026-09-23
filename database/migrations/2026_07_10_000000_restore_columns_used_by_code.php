<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migracja naprawcza po 2026_02_04_000001/000002 (cleanup duplicates).
     */
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            if (! Schema::hasColumn('auctions', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('ends_at');
            }
            if (! Schema::hasColumn('auctions', 'final_bid_amount')) {
                $table->decimal('final_bid_amount', 10, 2)->nullable()->after('current_price');
            }
            if (! Schema::hasColumn('auctions', 'times_extended')) {
                $table->integer('times_extended')->default(0)->after('extension_minutes');
            }
        });

        Schema::table('bids', function (Blueprint $table) {
            if (! Schema::hasColumn('bids', 'is_winning_bid')) {
                if (Schema::hasIndex('bids', 'bids_auction_id_is_winning_bid_index')) {
                    $table->dropIndex('bids_auction_id_is_winning_bid_index');
                }
                $table->boolean('is_winning_bid')->default(false)->after('amount');
                $table->index(['auction_id', 'is_winning_bid']);
            }
        });

        // (new_bid_in_your_auction, auction_activated, price_changed, ...) —
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('type', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('bids', function (Blueprint $table) {
            if (Schema::hasColumn('bids', 'is_winning_bid')) {
                if (Schema::hasIndex('bids', 'bids_auction_id_is_winning_bid_index')) {
                    $table->dropIndex('bids_auction_id_is_winning_bid_index');
                }
                $table->dropColumn('is_winning_bid');
            }
        });

        Schema::table('auctions', function (Blueprint $table) {
            foreach (['ended_at', 'final_bid_amount', 'times_extended'] as $column) {
                if (Schema::hasColumn('auctions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
