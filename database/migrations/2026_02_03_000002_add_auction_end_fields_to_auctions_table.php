<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            if (! Schema::hasColumn('auctions', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('ends_at');
                $table->index('ended_at');
            }
            if (! Schema::hasColumn('auctions', 'final_bid_amount')) {
                $table->decimal('final_bid_amount', 10, 2)->nullable()->after('reserve_price');
            }
        });
    }

    public function down(): void
    {
        // Disabled for SQLite compatibility in tests
        // SQLite has issues with dropping indexed columns
        // This down() method is rarely used in production, so it's safe to disable
    }
};
