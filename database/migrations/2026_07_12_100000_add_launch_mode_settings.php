<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('platform_settings', 'only_admin_can_list')) {
                $table->boolean('only_admin_can_list')->default(false);
            }
            if (! Schema::hasColumn('platform_settings', 'bidding_enabled')) {
                $table->boolean('bidding_enabled')->default(true);
            }
            if (! Schema::hasColumn('platform_settings', 'google_analytics_id')) {
                $table->string('google_analytics_id', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            foreach (['only_admin_can_list', 'bidding_enabled', 'google_analytics_id'] as $column) {
                if (Schema::hasColumn('platform_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
