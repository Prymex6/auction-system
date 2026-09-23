<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->decimal('rating', 2, 1)->default(5)->change();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['auction_id']);
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('auction_id')->nullable()->change();
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign('auction_id')->references('id')->on('auctions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->integer('rating')->change();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['auction_id']);
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('auction_id')->nullable(false)->change();
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign('auction_id')->references('id')->on('auctions')->cascadeOnDelete();
        });
    }
};
