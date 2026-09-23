<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', [
                'bid_placed', 'outbid', 'auction_won', 'auction_ended',
                'auction_extended', 'new_bid',
                'message_received', 'message_response',
                'listing_approved', 'listing_rejected',
                'system_alert', 'premium_expired',
            ]);
            $table->string('title');
            $table->text('message');
            $table->foreignId('auction_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('related_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('data')->nullable(); // Extra payload
            $table->timestamps();

            $table->index(['user_id', 'read', 'created_at']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
