<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add columns only if they do not already exist (safety for repeated runs)
        if (! Schema::hasColumn('bids', 'auto_bid_status') || ! Schema::hasColumn('bids', 'auto_bid_max_amount') || ! Schema::hasColumn('bids', 'increment_used') || ! Schema::hasColumn('bids', 'is_auto_bid') || ! Schema::hasColumn('bids', 'placed_at')) {
            Schema::table('bids', function (Blueprint $table) {
                // Auto-bid support
                if (! Schema::hasColumn('bids', 'auto_bid_status')) {
                    $table->enum('auto_bid_status', ['active', 'won', 'outbid'])->nullable()->after('amount');
                }

                if (! Schema::hasColumn('bids', 'auto_bid_max_amount')) {
                    $table->decimal('auto_bid_max_amount', 10, 2)->nullable()->after('auto_bid_status');
                }

                if (! Schema::hasColumn('bids', 'increment_used')) {
                    $table->decimal('increment_used', 10, 2)->nullable()->after('auto_bid_max_amount');
                }

                if (! Schema::hasColumn('bids', 'is_auto_bid')) {
                    $table->boolean('is_auto_bid')->default(false)->after('increment_used');
                }

                if (! Schema::hasColumn('bids', 'placed_at')) {
                    $table->timestamp('placed_at')->nullable()->after('is_auto_bid');
                }
            });
        }
    }

    public function down(): void
    {
        // Drop only columns that exist
        Schema::table('bids', function (Blueprint $table) {
            $cols = [];
            foreach (['auto_bid_status', 'auto_bid_max_amount', 'increment_used', 'is_auto_bid', 'placed_at'] as $c) {
                if (Schema::hasColumn('bids', $c)) {
                    $cols[] = $c;
                }
            }

            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
