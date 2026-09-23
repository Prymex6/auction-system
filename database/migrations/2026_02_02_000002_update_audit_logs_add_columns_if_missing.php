<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (! Schema::hasColumn('audit_logs', 'meta')) {
                    $table->json('meta')->nullable()->after('model_id');
                }
                if (! Schema::hasColumn('audit_logs', 'reason')) {
                    $table->string('reason')->nullable()->after('meta');
                }
                if (! Schema::hasColumn('audit_logs', 'ip_address')) {
                    $table->string('ip_address')->nullable()->after('reason');
                }
                if (! Schema::hasColumn('audit_logs', 'user_agent')) {
                    $table->string('user_agent')->nullable()->after('ip_address');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (Schema::hasColumn('audit_logs', 'user_agent')) {
                    $table->dropColumn('user_agent');
                }
                if (Schema::hasColumn('audit_logs', 'ip_address')) {
                    $table->dropColumn('ip_address');
                }
                if (Schema::hasColumn('audit_logs', 'reason')) {
                    $table->dropColumn('reason');
                }
                if (Schema::hasColumn('audit_logs', 'meta')) {
                    $table->dropColumn('meta');
                }
            });
        }
    }
};
