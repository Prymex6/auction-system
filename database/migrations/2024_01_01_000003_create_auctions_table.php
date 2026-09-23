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
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('start_price', 10, 2);
            $table->decimal('current_price', 10, 2);
            $table->decimal('reserve_price', 10, 2)->nullable(); // Cena minimalna
            $table->unsignedBigInteger('winner_id')->nullable(); // Zwycięzca
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->enum('status', ['pending', 'active', 'ended', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->foreign('winner_id')->references('id')->on('users')->onDelete('set null');

            $table->index('user_id');
            $table->index('winner_id');
            $table->index('status');
            $table->index('ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
