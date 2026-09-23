<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'is_approved')) {
                $table->boolean('is_approved')->default(true)->after('content');
            }
            if (! Schema::hasColumn('messages', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('is_approved');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
            if (Schema::hasColumn('messages', 'is_approved')) {
                $table->dropColumn('is_approved');
            }
        });
    }
};
