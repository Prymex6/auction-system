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
        if (Schema::hasIndex('bids', 'bids_auction_id_is_winning_bid_index')) {
            Schema::table('bids', function (Blueprint $table) {
                $table->dropIndex('bids_auction_id_is_winning_bid_index');
            });
        }

        Schema::table('bids', function (Blueprint $table) {
            $columnsToRemove = [
                'is_winning_bid',
                'auto_bid_status',
                'increment_used',
                'placed_at',                // duplicate of created_at
                'auto_bid_max_amount',      // duplicate of max_auto_bid
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('bids', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
