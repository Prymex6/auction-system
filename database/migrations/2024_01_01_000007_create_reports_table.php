<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');

            // Polymorphic relation for reportable (User, Listing, Message, etc.)
            $table->string('reportable_type');
            $table->unsignedBigInteger('reportable_id');

            $table->enum('reason', ['spam', 'inappropriate', 'fraud', 'dead_pigeon', 'other']);
            $table->text('description');
            $table->enum('status', ['pending', 'reviewing', 'resolved', 'dismissed'])->default('pending');

            // Review fields
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('resolution_reason')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('reported_by');
            $table->index(['reportable_type', 'reportable_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
