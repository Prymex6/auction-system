<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('auctions')) {
            return;
        }

        Schema::table('auctions', function (Blueprint $table) {
            if (! Schema::hasColumn('auctions', 'starting_price')) {
                $table->decimal('starting_price', 10, 2)->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('auctions', 'current_bid')) {
                $table->decimal('current_bid', 10, 2)->nullable()->after('starting_price');
            }

            if (! Schema::hasColumn('auctions', 'start_time')) {
                $table->timestamp('start_time')->nullable()->after('current_bid');
            }

            if (! Schema::hasColumn('auctions', 'end_time')) {
                $table->timestamp('end_time')->nullable()->after('start_time');
            }

            if (! Schema::hasColumn('auctions', 'anti_sniper_enabled')) {
                $table->boolean('anti_sniper_enabled')->default(true)->nullable()->after('end_time');
            }
            if (! Schema::hasColumn('auctions', 'sniper_threshold')) {
                $table->integer('sniper_threshold')->default(5)->nullable()->after('anti_sniper_enabled');
            }
            if (! Schema::hasColumn('auctions', 'extension_minutes')) {
                $table->integer('extension_minutes')->default(5)->nullable()->after('sniper_threshold');
            }
            if (! Schema::hasColumn('auctions', 'times_extended')) {
                $table->integer('times_extended')->default(0)->nullable()->after('extension_minutes');
            }

            if (! Schema::hasColumn('auctions', 'highest_bidder_id')) {
                $table->unsignedBigInteger('highest_bidder_id')->nullable()->after('winner_id');
            }
            if (! Schema::hasColumn('auctions', 'bids_count')) {
                $table->unsignedInteger('bids_count')->default(0)->after('highest_bidder_id');
            }

            if (Schema::hasColumn('auctions', 'winner_id')) {
                // ensure winner_id nullable (best-effort; may not be supported on sqlite without DBAL)
                try {
                    $table->foreignId('winner_id')->nullable()->change();
                } catch (Throwable $e) {
                    // ignore; sqlite may not support change without DBAL
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('auctions')) {
            return;
        }

        Schema::table('auctions', function (Blueprint $table) {
            if (Schema::hasColumn('auctions', 'starting_price')) {
                $table->dropColumn('starting_price');
            }
            if (Schema::hasColumn('auctions', 'current_bid')) {
                $table->dropColumn('current_bid');
            }
            if (Schema::hasColumn('auctions', 'start_time')) {
                $table->dropColumn('start_time');
            }
            if (Schema::hasColumn('auctions', 'end_time')) {
                $table->dropColumn('end_time');
            }
            if (Schema::hasColumn('auctions', 'anti_sniper_enabled')) {
                $table->dropColumn(['anti_sniper_enabled', 'sniper_threshold', 'extension_minutes', 'times_extended']);
            }
        });
    }
};
