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
        // Update users table with new fields
        Schema::table('users', function (Blueprint $table) {
            $table->string('address')->nullable()->after('bio');
            $table->string('city')->nullable()->after('address');
            $table->string('postcode')->nullable()->after('city');
            $table->string('country')->nullable()->after('postcode');

            $table->boolean('two_factor_enabled')->default(false)->after('country');
            $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');

            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();

            $table->boolean('is_banned')->default(false);
            $table->text('ban_reason')->nullable();
            $table->timestamp('ban_until')->nullable();

            $table->enum('premium_plan', ['free', '1month', '3months', '12months'])->default('free');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'address', 'city', 'postcode', 'country',
                'two_factor_enabled', 'two_factor_secret',
                'phone_verified_at', 'last_login_at', 'last_login_ip',
                'is_banned', 'ban_reason', 'ban_until',
                'premium_plan',
            ]);
        });
    }
};
