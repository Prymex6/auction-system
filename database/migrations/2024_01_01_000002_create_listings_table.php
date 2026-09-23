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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->string('breed'); // Rasa gołębia
            $table->integer('age')->nullable(); // Wiek w dniach/miesiącach
            $table->enum('gender', ['M', 'F', 'unknown'])->default('unknown');
            $table->string('color')->nullable(); // Kolor upierzenia
            $table->text('health_status')->nullable(); // Stan zdrowia
            $table->decimal('price', 10, 2)->nullable();
            $table->enum('type', ['buy_now', 'auction', 'both'])->default('buy_now');
            $table->enum('status', ['active', 'sold', 'pending', 'hidden', 'rejected'])->default('pending');
            $table->integer('stock')->default(1);
            $table->string('location')->nullable(); // Lokalizacja
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('status');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
