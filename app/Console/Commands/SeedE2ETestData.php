<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SeedE2ETestData extends Command
{
    protected $signature = 'e2e:seed';

    protected $description = 'Utworz/zresetuj stale konta testowe do testow E2E (Playwright)';

    public const ADMIN_EMAIL = 'e2e_admin@example.com';

    public const ADMIN_NAME = 'e2e_admin';

    public const USER_EMAIL = 'e2e_user@example.com';

    public const USER_NAME = 'e2e_user';

    public const SELLER_EMAIL = 'e2e_seller@example.com';

    public const SELLER_NAME = 'e2e_seller';

    public const PASSWORD = 'E2ETestPass123!';

    public function handle(): void
    {
        if (app()->environment('production')) {
            $this->error('Ta komenda jest zablokowana na produkcji - tylko do testów E2E lokalnie/CI.');

            return;
        }

        $admin = User::updateOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => self::ADMIN_NAME,
                'first_name' => 'E2E',
                'last_name' => 'Admin',
                'password' => Hash::make(self::PASSWORD),
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $user = User::updateOrCreate(
            ['email' => self::USER_EMAIL],
            [
                'name' => self::USER_NAME,
                'first_name' => 'E2E',
                'last_name' => 'User',
                'password' => Hash::make(self::PASSWORD),
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $seller = User::updateOrCreate(
            ['email' => self::SELLER_EMAIL],
            [
                'name' => self::SELLER_NAME,
                'first_name' => 'E2E',
                'last_name' => 'Seller',
                'password' => Hash::make(self::PASSWORD),
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $biddingAuction = Auction::updateOrCreate(
            ['title' => 'E2E Test Aukcja Licytacyjna'],
            [
                'user_id' => $seller->id,
                'breed' => 'Janssen',
                'gender' => 'samiec',
                'year' => 2024,
                'size' => 'sredni',
                'color' => 'Niebieska',
                'color_code' => 'NIEB',
                'type' => 'auction',
                'start_price' => 100,
                'current_price' => 100,
                'minimum_increase' => 1,
                'status' => 'active',
                'started_at' => now(),
                'ends_at' => now()->addDays(7),
                'pigeon_images' => ['images/auction/placeholder.jpg'],
            ]
        );

        $biddingAuction->bids()->delete();

        Auction::updateOrCreate(
            ['title' => 'E2E Test Oferta Kup Teraz'],
            [
                'user_id' => $seller->id,
                'breed' => 'Janssen',
                'gender' => 'samica',
                'year' => 2024,
                'size' => 'sredni',
                'color' => 'Niebieska',
                'color_code' => 'NIEB',
                'type' => 'buy_now',
                'start_price' => 200,
                'current_price' => 200,
                'minimum_increase' => 1,
                'status' => 'active',
                'started_at' => now(),
                'ends_at' => now()->addDays(7),
                'pigeon_images' => ['images/auction/placeholder.jpg'],
            ]
        );

        $this->info('E2E test data ready: '.self::ADMIN_EMAIL.', '.self::USER_EMAIL.', '.self::SELLER_EMAIL);
    }
}
