<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            // Anti-sniper fields
            $table->boolean('anti_sniper_enabled')->default(true)->after('ends_at');
            $table->integer('sniper_threshold')->default(5)->after('anti_sniper_enabled'); // minutes
            $table->integer('extension_minutes')->default(5)->after('sniper_threshold'); // how long to extend
            $table->integer('times_extended')->default(0)->after('extension_minutes');
            $table->timestamp('original_end_time')->nullable()->after('times_extended');

            // Collection batch support
            $table->foreignId('auction_batch_id')->nullable()->constrained('collection_batches')->onDelete('set null')->after('original_end_time');
        });
    }

    public function down(): void
    {
        // Check if foreign key exists before dropping
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'auctions' 
            AND CONSTRAINT_NAME = 'auctions_auction_batch_id_foreign'
        ");

        if (! empty($foreignKeys)) {
            Schema::table('auctions', function (Blueprint $table) {
                $table->dropForeign(['auction_batch_id']);
            });
        }

        Schema::table('auctions', function (Blueprint $table) {
            // Drop columns only if they exist
            if (Schema::hasColumn('auctions', 'anti_sniper_enabled')) {
                $table->dropColumn('anti_sniper_enabled');
            }
            if (Schema::hasColumn('auctions', 'sniper_threshold')) {
                $table->dropColumn('sniper_threshold');
            }
            if (Schema::hasColumn('auctions', 'extension_minutes')) {
                $table->dropColumn('extension_minutes');
            }
            if (Schema::hasColumn('auctions', 'times_extended')) {
                $table->dropColumn('times_extended');
            }
            if (Schema::hasColumn('auctions', 'original_end_time')) {
                $table->dropColumn('original_end_time');
            }
            if (Schema::hasColumn('auctions', 'auction_batch_id')) {
                $table->dropColumn('auction_batch_id');
            }
        });
    }
};
