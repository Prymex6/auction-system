<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Nie ma konceptu "minimum reserve price" w Twoim modelu biznesowym.
     */
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            if (Schema::hasColumn('auctions', 'reserve_price')) {
                $table->dropColumn('reserve_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
