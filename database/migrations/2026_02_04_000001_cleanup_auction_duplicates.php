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
            $columnsToRemove = [
                'title',
                'breed',
                'gender',
                'year',
                'size',
                'color',
                'weight',
                'ring_number',
                'origin_source',
                'description',
                'images',
                'pedigree_images',
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('auctions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasIndex('auctions', 'auctions_ended_at_index')) {
            Schema::table('auctions', function (Blueprint $table) {
                $table->dropIndex('auctions_ended_at_index');
            });
        }

        Schema::table('auctions', function (Blueprint $table) {
            $duplicates = [
                'starting_price',  // -> use start_price
                'current_bid',     // -> use current_price
                'start_time',      // -> use started_at
                'end_time',        // -> use ends_at
                'ended_at',
                'final_bid_amount',
                'buy_now_price',   // nie potrzebna
                'minimum_increase', // nie potrzebna
                'highest_bidder_id', // -> use winner_id
                'bids_count',      // obliczane z COUNT(bids)
                'original_end_time', // nie potrzebna
                'times_extended',  // nie potrzebna
                'auction_type',    // nie potrzebna
                'allows_bidding',
                'allows_best_offer', // nie implementowane
                'is_featured',     // nie implementowane
                'offers_delivery', // nie implementowane
                'delivery_cost',   // nie implementowane
                'duration_days',   // obliczane z ends_at - started_at
            ];

            foreach ($duplicates as $column) {
                if (Schema::hasColumn('auctions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {});
    }
};
