<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'show_as_home_section')) {
                $table->boolean('show_as_home_section')->default(false)->after('is_featured')
                    ->comment('Pokaż jako osobną sekcję na stronie głównej (jak "Kup teraz"), nie tylko kafelek w Wyróżnionych kategoriach');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'show_as_home_section')) {
                $table->dropColumn('show_as_home_section');
            }
        });
    }
};
