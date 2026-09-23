<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            if (! Schema::hasColumn('user_devices', 'device_token')) {
                $table->string('device_token')->nullable()->after('user_id');
                $table->index(['user_id', 'device_token']);
            }
        });
    }

    public function down(): void
    {
        // SQLite compatibility: drop index separately first
        Schema::table('user_devices', function (Blueprint $table) {
            if (Schema::hasColumn('user_devices', 'device_token') && DB::getDriverName() !== 'sqlite') {
                $table->dropIndex(['user_id', 'device_token']);
            }
        });

        Schema::table('user_devices', function (Blueprint $table) {
            if (Schema::hasColumn('user_devices', 'device_token')) {
                $table->dropColumn('device_token');
            }
        });
    }
};
