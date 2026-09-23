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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_token')->unique();
            $table->string('device_name')->nullable(); // np. "Chrome on Windows"
            $table->string('device_type')->default('web'); // web, mobile, tablet
            $table->string('ip_address')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_current']);
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->unique();
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sound_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('bid_notifications')->default(true);
            $table->boolean('message_notifications')->default(true);
            $table->boolean('auction_end_notifications')->default(true);
            $table->timestamps();
        });

        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'category_type')) {
                $table->enum('category_type', ['standard', 'smart'])->default('standard')->after('description');
            }
            if (! Schema::hasColumn('categories', 'smart_filter')) {
                $table->json('smart_filter')->nullable()->after('category_type')->comment('Filtry dla smart kategorii (np. user_id hodowcy)');
            }
            if (! Schema::hasColumn('categories', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('smart_filter')->comment('Wyświetlaj na stronie głównej');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
            if (Schema::hasColumn('categories', 'smart_filter')) {
                $table->dropColumn('smart_filter');
            }
            if (Schema::hasColumn('categories', 'category_type')) {
                $table->dropColumn('category_type');
            }
        });

        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('user_devices');
    }
};
