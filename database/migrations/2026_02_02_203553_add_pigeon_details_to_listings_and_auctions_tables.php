<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'age')) {
                $table->dropColumn('age');
            }
            if (Schema::hasColumn('listings', 'health_status')) {
                $table->dropColumn('health_status');
            }
            if (Schema::hasColumn('listings', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('listings', 'stock')) {
                $table->dropColumn('stock');
            }
            if (Schema::hasColumn('listings', 'location')) {
                $table->dropColumn('location');
            }
        });

        Schema::table('listings', function (Blueprint $table) {
            if (! Schema::hasColumn('listings', 'year')) {
                $table->integer('year')->nullable()->after('breed');
            }
            if (! Schema::hasColumn('listings', 'size')) {
                $table->string('size', 20)->nullable()->after('year');
            }
            if (! Schema::hasColumn('listings', 'color_code')) {
                $table->string('color_code', 10)->nullable()->after('color');
            }
            if (! Schema::hasColumn('listings', 'pigeon_images')) {
                $table->json('pigeon_images')->nullable()->after('description');
            }
            if (! Schema::hasColumn('listings', 'pedigree_images')) {
                $table->json('pedigree_images')->nullable()->after('pigeon_images');
            }
        });

        if ($driver === 'sqlite') {
            Schema::create('listings_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('category_id')->nullable();
                $table->string('title');
                $table->text('description');
                $table->string('breed');
                $table->integer('year')->nullable();
                $table->string('gender', 20)->default('samiec');
                $table->string('color')->nullable();
                $table->string('color_code', 10)->nullable();
                $table->string('size', 20)->nullable();
                $table->json('pigeon_images')->nullable();
                $table->json('pedigree_images')->nullable();
                $table->string('type', 20)->default('buy_now');
                $table->string('status', 20)->default('pending');
                $table->timestamps();
                $table->softDeletes();
            });

            DB::statement("INSERT INTO listings_new (id, user_id, category_id, title, description, breed, year, gender, color, color_code, size, pigeon_images, pedigree_images, type, status, created_at, updated_at, deleted_at)
                SELECT id, user_id, category_id, title, description, breed, year, 
                    CASE 
                        WHEN gender = 'M' THEN 'samiec'
                        WHEN gender = 'F' THEN 'samica'
                        ELSE 'golab_mlody'
                    END as gender,
                    color, color_code, size, pigeon_images, pedigree_images, type, status, created_at, updated_at, deleted_at
                FROM listings");

            Schema::drop('listings');
            Schema::rename('listings_new', 'listings');
        } else {
            if (! Schema::hasColumn('listings', 'gender_new')) {
                Schema::table('listings', function (Blueprint $table) {
                    $table->string('gender_new', 20)->default('samiec')->after('gender');
                });
            }

            DB::table('listings')->where('gender', 'M')->update(['gender_new' => 'samiec']);
            DB::table('listings')->where('gender', 'F')->update(['gender_new' => 'samica']);
            DB::table('listings')->whereIn('gender', ['unknown', ''])->orWhereNull('gender')->update(['gender_new' => 'golab_mlody']);

            Schema::table('listings', function (Blueprint $table) {
                $table->dropColumn('gender');
            });

            DB::statement("ALTER TABLE listings CHANGE gender_new gender VARCHAR(20) NOT NULL DEFAULT 'samiec'");
        }

        Schema::table('auctions', function (Blueprint $table) {
            if (! Schema::hasColumn('auctions', 'minimum_increase')) {
                $table->decimal('minimum_increase', 10, 2)->default(1.00)->after('current_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'year')) {
                $table->dropColumn('year');
            }
            if (Schema::hasColumn('listings', 'size')) {
                $table->dropColumn('size');
            }
            if (Schema::hasColumn('listings', 'color_code')) {
                $table->dropColumn('color_code');
            }
            if (Schema::hasColumn('listings', 'pigeon_images')) {
                $table->dropColumn('pigeon_images');
            }
            if (Schema::hasColumn('listings', 'pedigree_images')) {
                $table->dropColumn('pedigree_images');
            }
        });

        Schema::table('listings', function (Blueprint $table) {
            if (! Schema::hasColumn('listings', 'age')) {
                $table->integer('age')->nullable();
            }
            if (! Schema::hasColumn('listings', 'health_status')) {
                $table->text('health_status')->nullable();
            }
            if (! Schema::hasColumn('listings', 'price')) {
                $table->decimal('price', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('listings', 'stock')) {
                $table->integer('stock')->default(1);
            }
            if (! Schema::hasColumn('listings', 'location')) {
                $table->string('location')->nullable();
            }
        });

        // Konwersja gender z powrotem
        if ($driver === 'mysql') {
            DB::table('listings')->where('gender', 'samiec')->update(['gender' => 'M']);
            DB::table('listings')->where('gender', 'samica')->update(['gender' => 'F']);
            DB::table('listings')->where('gender', 'golab_mlody')->update(['gender' => 'unknown']);

            Schema::table('listings', function (Blueprint $table) {
                $table->enum('gender', ['M', 'F', 'unknown'])->default('unknown')->change();
            });
        }

        Schema::table('auctions', function (Blueprint $table) {
            if (Schema::hasColumn('auctions', 'minimum_increase')) {
                $table->dropColumn('minimum_increase');
            }
        });
    }
};
