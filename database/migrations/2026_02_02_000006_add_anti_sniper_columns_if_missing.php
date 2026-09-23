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
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('auctions')) {
            return;
        }

        Schema::table('auctions', function (Blueprint $table) {
            $cols = [];
            foreach (['anti_sniper_enabled', 'sniper_threshold', 'extension_minutes', 'times_extended'] as $col) {
                if (Schema::hasColumn('auctions', $col)) {
                    $cols[] = $col;
                }
            }
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
