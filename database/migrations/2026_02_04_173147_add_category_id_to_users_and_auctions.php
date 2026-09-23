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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            }
        });

        Schema::table('auctions', function (Blueprint $table) {
            if (! Schema::hasColumn('auctions', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignIdFor('categories', 'category_id');
        });

        Schema::table('auctions', function (Blueprint $table) {
            $table->dropForeignIdFor('categories', 'category_id');
        });
    }
};
