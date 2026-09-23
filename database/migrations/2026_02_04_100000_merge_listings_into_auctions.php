<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained('categories')->onDelete('set null');
            $table->string('title')->after('category_id');
            $table->text('description')->nullable()->after('title');
            $table->string('breed')->after('description'); // Rasa gołębia
            $table->integer('year')->nullable()->after('breed'); // Rok urodzenia
            $table->string('gender', 20)->after('year'); // samiec|samica|golab_mlody
            $table->string('color')->nullable()->after('gender'); // Kolor upierzenia
            $table->string('color_code', 10)->nullable()->after('color');
            $table->string('ring_number', 50)->nullable()->after('color_code'); // Numer obrączki
            $table->string('size', 20)->nullable()->after('ring_number'); // maly|sredni|duzy
            $table->json('pigeon_images')->nullable()->after('size'); // Zdjęcia gołębia
            $table->json('pedigree_images')->nullable()->after('pigeon_images'); // Zdjęcia rodowodu
            $table->enum('type', ['buy_now', 'auction', 'both'])->default('auction')->after('pedigree_images');
            $table->string('rejection_reason')->nullable()->after('type'); // Powód odrzucenia
        });

        if (DB::getDriverName() === 'sqlite') {
            $columns = [
                'category_id', 'title', 'description', 'breed', 'year', 'gender',
                'color', 'color_code', 'ring_number', 'size', 'pigeon_images',
                'pedigree_images', 'type', 'rejection_reason',
            ];
            $sets = implode(', ', array_map(
                fn ($col) => "$col = (SELECT l.$col FROM listings l WHERE l.id = auctions.listing_id)",
                $columns
            ));
            DB::statement("UPDATE auctions SET $sets WHERE listing_id IN (SELECT id FROM listings)");
        } else {
            DB::statement('
                UPDATE auctions a
                INNER JOIN listings l ON a.listing_id = l.id
                SET
                    a.category_id = l.category_id,
                    a.title = l.title,
                    a.description = l.description,
                    a.breed = l.breed,
                    a.year = l.year,
                    a.gender = l.gender,
                    a.color = l.color,
                    a.color_code = l.color_code,
                    a.ring_number = l.ring_number,
                    a.size = l.size,
                    a.pigeon_images = l.pigeon_images,
                    a.pedigree_images = l.pedigree_images,
                    a.type = l.type,
                    a.rejection_reason = l.rejection_reason
            ');
        }

        Schema::table('auctions', function (Blueprint $table) {
            $table->dropForeign(['listing_id']);
            $table->dropColumn('listing_id');
        });

        // Watchlist
        if (Schema::hasTable('watchlist')) {
            Schema::table('watchlist', function (Blueprint $table) {
                if (Schema::hasColumn('watchlist', 'listing_id')) {
                    $table->dropForeign(['listing_id']);
                    $table->renameColumn('listing_id', 'auction_id');
                }
            });

            Schema::table('watchlist', function (Blueprint $table) {
                if (Schema::hasColumn('watchlist', 'auction_id')) {
                    $table->foreign('auction_id')->references('id')->on('auctions')->onDelete('cascade');
                }
            });
        }

        if (Schema::hasTable('messages')) {
            if (Schema::hasColumn('messages', 'listing_id')) {
                Schema::table('messages', function (Blueprint $table) {
                    $table->dropForeign(['listing_id']);
                    $table->renameColumn('listing_id', 'auction_id');
                });

                Schema::table('messages', function (Blueprint $table) {
                    $table->foreign('auction_id')->references('id')->on('auctions')->onDelete('cascade');
                });
            }
        }

        Schema::dropIfExists('listings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate listings table
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('breed');
            $table->integer('year')->nullable();
            $table->string('gender', 20);
            $table->string('color')->nullable();
            $table->string('color_code', 10)->nullable();
            $table->string('ring_number', 50)->nullable();
            $table->string('size', 20)->nullable();
            $table->json('pigeon_images')->nullable();
            $table->json('pedigree_images')->nullable();
            $table->enum('type', ['buy_now', 'auction', 'both'])->default('buy_now');
            $table->enum('status', ['active', 'sold', 'pending', 'hidden', 'rejected'])->default('pending');
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('status');
            $table->index('type');
        });

        // Copy data back to listings
        DB::statement('
            INSERT INTO listings (id, user_id, category_id, title, description, breed, year, gender, color, color_code, ring_number, size, pigeon_images, pedigree_images, type, status, rejection_reason, created_at, updated_at)
            SELECT id, user_id, category_id, title, description, breed, year, gender, color, color_code, ring_number, size, pigeon_images, pedigree_images, type, status, rejection_reason, created_at, updated_at
            FROM auctions
        ');

        // Add listing_id back to auctions
        Schema::table('auctions', function (Blueprint $table) {
            $table->foreignId('listing_id')->after('id')->constrained('listings')->onDelete('cascade');
        });

        // Update auctions with listing_id
        DB::statement('UPDATE auctions SET listing_id = id');

        // Revert watchlist
        if (Schema::hasTable('watchlist')) {
            Schema::table('watchlist', function (Blueprint $table) {
                if (Schema::hasColumn('watchlist', 'auction_id')) {
                    $table->dropForeign(['auction_id']);
                    $table->renameColumn('auction_id', 'listing_id');
                }
            });

            Schema::table('watchlist', function (Blueprint $table) {
                if (Schema::hasColumn('watchlist', 'listing_id')) {
                    $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
                }
            });
        }

        // Revert messages
        if (Schema::hasTable('messages')) {
            if (Schema::hasColumn('messages', 'auction_id')) {
                Schema::table('messages', function (Blueprint $table) {
                    $table->dropForeign(['auction_id']);
                    $table->renameColumn('auction_id', 'listing_id');
                });

                Schema::table('messages', function (Blueprint $table) {
                    $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
                });
            }
        }

        // Remove columns from auctions
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'category_id',
                'title',
                'description',
                'breed',
                'year',
                'gender',
                'color',
                'color_code',
                'ring_number',
                'size',
                'pigeon_images',
                'pedigree_images',
                'type',
                'rejection_reason',
            ]);
        });
    }
};
