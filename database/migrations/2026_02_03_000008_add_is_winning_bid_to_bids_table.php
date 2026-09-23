<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bids', function (Blueprint $table) {
            if (! Schema::hasColumn('bids', 'is_winning_bid')) {
                $table->boolean('is_winning_bid')->default(false)->after('amount');
                $table->index(['auction_id', 'is_winning_bid']);
            }
        });
    }

    public function down(): void
    {
        // SQLite compatibility: drop index separately first
        Schema::table('bids', function (Blueprint $table) {
            if (Schema::hasColumn('bids', 'is_winning_bid') && DB::getDriverName() !== 'sqlite') {
                $table->dropIndex(['auction_id', 'is_winning_bid']);
            }
        });

        Schema::table('bids', function (Blueprint $table) {
            if (Schema::hasColumn('bids', 'is_winning_bid')) {
                $table->dropColumn('is_winning_bid');
            }
        });
    }
};
