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
        Schema::table('user_devices', function (Blueprint $table) {
            $columnsToRemove = [
                'browser',
                'browser_version',
                'os',
                'os_version',
                'country',
                'city',
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('user_devices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('user_devices', function (Blueprint $table) {
            if (! Schema::hasColumn('user_devices', 'device_token')) {
                $table->string('device_token')->nullable()->after('is_current');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
