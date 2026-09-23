<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Category;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Production-ready seeder z realistycznymi danymi
 */
class ProductionDatabaseSeeder extends Seeder
{
    private array $polishCities = [
        'Warszawa', 'Kraków', 'Wrocław', 'Poznań', 'Gdańsk', 'Szczecin',
        'Bydgoszcz', 'Lublin', 'Katowice', 'Białystok', 'Gdynia', 'Częstochowa',
        'Radom', 'Sosnowiec', 'Toruń', 'Kielce', 'Gliwice', 'Zabrze',
        'Bytom', 'Olsztyn', 'Bielsko-Biała', 'Rzeszów', 'Ruda Śląska', 'Rybnik',
    ];

    private array $pigeonBreeds = [
        'Pocztowy Belgijski', 'Pocztowy Holenderski', 'Pocztowy Polski',
        'Pocztowy Angielski', 'Pocztowy Niemiecki', 'Janssen',
        'Van Loon', 'Heremans-Ceusters', 'Gaby Vandenabeele',
        'De Rauw-Sablon', 'Sion', 'Aarden', 'Van den Bulck',
    ];

    private array $pigeonColors = [
        'Niebieska', 'Niebieska pstra', 'Czerwona', 'Czerwona pstra',
        'Żółta', 'Biała', 'Czarna', 'Srebrna', 'Grizzle', 'Dun',
    ];

    private array $userBios = [
        'Hodowca z 15-letnim doświadczeniem. Specjalizacja: gołębie pocztowe długodystansowe.',
        'Pasjonat gołębi wyścigowych. Wielokrotny zwycięzca krajowych konkursów.',
        'Rodzinna hodowla od trzech pokoleń. Linie belgijskie i holenderskie.',
        'Profesjonalny hodowca. Champion w kategorii młode gołębie 2022-2024.',
        'Hodowla rasowa premium. Certyfikowane linie rodowodowe.',
        'Specjalista od grzywaczów. Sędzia na wystawach krajowych.',
        'Hodowla elitarna. Współpraca z najlepszymi hodowcami europejskimi.',
        'Pasja i tradycja. Uczestnik lotów krajowych i międzynarodowych.',
    ];

    private array $auctionTitles = [
        'Syn olimpijskiego kampiona {{breed}}',
        'Młody {{breed}} {{year}} z TOP linii',
        'Samiec {{breed}} - gotowy do sezonu',
        'Samica {{breed}} - córka asa',
        'Para {{breed}} - idealna do hodowli',
        'Młodziak {{breed}} {{color}} - wielki potencjał',
        'Champion {{breed}} - sprawdzony w lotach',
        'Elitarny {{breed}} z certyfikatem',
        '{{breed}} {{color}} - linia belgijska',
        'Potomek zwycięzcy Golden Race {{year}}',
    ];

    /**
     * A password for a seeded account, or a refusal.
     *
     * Seeding must never invent one: an account created with a password that
     * is written down in the repository is a way in, not a convenience.
     */
    private function requiredPassword(string $key): string
    {
        $password = config($key);
        if (blank($password)) {
            throw new RuntimeException($key.' must be set before seeding.');
        }

        return $password;
    }

    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🚀 Rozpoczynam seedowanie produkcyjne...');

            // 1. Kategorie
            $this->command->info('📂 Tworzę kategorie...');
            $categories = $this->seedCategories();

            $this->command->info('👥 Tworzę użytkowników...');
            $users = $this->seedUsers();

            $this->command->info('🏆 Tworzę aukcje...');
            $auctions = $this->seedAuctions($users, $categories);

            $this->command->info('💰 Dodaję licytacje...');
            $this->seedBids($auctions, $users);

            // 5. Recenzje
            $this->command->info('⭐ Tworzę recenzje...');
            $this->seedReviews($users);

            // 6. Blog
            $this->command->info('📝 Dodaję artykuły bloga...');
            $this->call(BlogPostSeeder::class);

            $this->command->info('✅ Seedowanie zakończone pomyślnie!');
            $this->printSummary($users, $auctions);
        });
    }

    private function seedCategories(): array
    {
        $categoriesData = [
            [
                'name' => 'Gołębie pocztowe',
                'slug' => 'golębie-pocztowe',
                'description' => 'Gołębie wyścigowe długo i krótkodystansowe',
                'category_type' => 'standard',
                'is_featured' => true,
            ],
            [
                'name' => 'Gołębie ozdobne',
                'slug' => 'golębie-ozdobne',
                'description' => 'Grzywacze, dublety i inne rasy ozdobne',
                'category_type' => 'standard',
                'is_featured' => true,
            ],
            [
                'name' => 'Młode gołębie '.date('Y'),
                'slug' => 'młode-golębie-'.date('Y'),
                'description' => 'Gołębie młode z bieżącego rocznika - idealne do hodowli',
                'category_type' => 'smart',
                'smart_filter' => ['year' => date('Y')],
                'is_featured' => true,
            ],
            [
                'name' => 'Najlepsi hodowcy',
                'slug' => 'najlepsi-hodowcy',
                'description' => 'Gołębie od uznanych hodowców z najwyższą reputacją',
                'category_type' => 'smart',
                'smart_filter' => ['min_price' => 1000],
                'is_featured' => true,
            ],
            [
                'name' => 'Najpopularniejsze',
                'slug' => 'najpopularniejsze',
                'description' => 'Aukcje gołębi z największą liczbą licytacji',
                'category_type' => 'smart',
                'smart_filter' => ['popular' => true],
                'is_featured' => true,
            ],
            [
                'name' => 'Najdroższe',
                'slug' => 'najdroższe',
                'description' => 'Premium gołębie pocztowe o najwyższej wartości',
                'category_type' => 'smart',
                'smart_filter' => ['min_price' => 1500],
                'is_featured' => true,
            ],
            [
                'name' => 'Kończące się dziś',
                'slug' => 'kończące-się-dziś',
                'description' => 'Aukcje kończące się w ciągu najbliższych 24 godzin',
                'category_type' => 'smart',
                'smart_filter' => ['ending_soon' => 24],
                'is_featured' => true,
            ],
            [
                'name' => 'Kup teraz',
                'slug' => 'kup-teraz',
                'description' => 'Oferty bez licytacji - kup gołębia natychmiast w stałej cenie',
                'category_type' => 'smart',
                'smart_filter' => ['type' => 'buy_now'],
                'is_featured' => true,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $categories[] = Category::create($data);
        }

        return $categories;
    }

    private function seedUsers(): array
    {
        $users = [];

        // Admin
        $adminData = [
            'name' => 'admin',
            'email' => 'admin@pigeon-auction.pl',
            'password' => bcrypt($this->requiredPassword('platform.admin.password')),
            'first_name' => 'Admin',
            'last_name' => 'System',
            'phone' => '+48500100100',
            'city' => 'Warszawa',
            'country' => 'PL',
            'is_admin' => true,
            'is_active' => true,
            'is_premium' => true,
            'email_verified_at' => now(),
        ];

        // Add phone_verified_at only if column exists
        if (Schema::hasColumn('users', 'phone_verified_at')) {
            $adminData['phone_verified_at'] = now();
        }

        $users[] = User::create($adminData);

        $hasPhoneVerified = Schema::hasColumn('users', 'phone_verified_at');

        for ($i = 1; $i <= 20; $i++) {
            $firstName = ['Jan', 'Piotr', 'Marek', 'Andrzej', 'Tomasz', 'Krzysztof', 'Zbigniew', 'Jerzy'][array_rand(['Jan', 'Piotr', 'Marek', 'Andrzej', 'Tomasz', 'Krzysztof', 'Zbigniew', 'Jerzy'])];
            $lastName = ['Kowalski', 'Nowak', 'Wiśniewski', 'Wójcik', 'Kowalczyk', 'Kamiński', 'Lewandowski', 'Zieliński'][array_rand(['Kowalski', 'Nowak', 'Wiśniewski', 'Wójcik', 'Kowalczyk', 'Kamiński', 'Lewandowski', 'Zieliński'])];
            $city = $this->polishCities[array_rand($this->polishCities)];

            $userData = [
                'name' => strtolower($firstName.$lastName.$i),
                'email' => strtolower($firstName.'.'.$lastName.$i.'@hodowla.pl'),
                'password' => bcrypt($this->requiredPassword('platform.seed_user_password')),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => '+48'.rand(500, 799).rand(100, 999).rand(100, 999),
                'city' => $city,
                'postcode' => rand(10, 99).'-'.rand(100, 999),
                'country' => 'PL',
                'bio' => $this->userBios[array_rand($this->userBios)],
                'is_active' => true,
                'is_premium' => $i <= 15,
                'premium_plan' => $i <= 15 ? ['1month', '3months', '12months'][array_rand([0, 1, 2])] : 'free',
                'premium_until' => $i <= 15 ? now()->addMonths(rand(1, 12)) : null,
                'email_verified_at' => now(),
                'listings_free_count' => rand(0, 5),
            ];

            if ($hasPhoneVerified && rand(0, 1)) {
                $userData['phone_verified_at'] = now();
            }

            $users[] = User::create($userData);
        }

        for ($i = 21; $i <= 50; $i++) {
            $firstName = ['Anna', 'Maria', 'Katarzyna', 'Małgorzata', 'Agnieszka', 'Barbara', 'Ewa', 'Zofia'][array_rand(['Anna', 'Maria', 'Katarzyna', 'Małgorzata', 'Agnieszka', 'Barbara', 'Ewa', 'Zofia'])];
            $lastName = ['Nowak', 'Kowalska', 'Wiśniewska', 'Dąbrowska', 'Lewandowska', 'Wójcik', 'Szymańska'][array_rand(['Nowak', 'Kowalska', 'Wiśniewska', 'Dąbrowska', 'Lewandowska', 'Wójcik', 'Szymańska'])];
            $city = $this->polishCities[array_rand($this->polishCities)];

            $userData = [
                'name' => strtolower(Str::slug($firstName.$lastName.$i)),
                'email' => strtolower($firstName.'.'.$lastName.$i.'@gmail.com'),
                'password' => bcrypt($this->requiredPassword('platform.seed_user_password')),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => '+48'.rand(500, 799).rand(100, 999).rand(100, 999),
                'city' => $city,
                'postcode' => rand(10, 99).'-'.rand(100, 999),
                'country' => 'PL',
                'is_active' => rand(0, 10) > 1, // 90% aktywnych
                'is_premium' => rand(0, 10) < 3, // 30% premium
                'premium_plan' => rand(0, 10) < 3 ? ['1month', '3months'][array_rand([0, 1])] : 'free',
                'premium_until' => rand(0, 10) < 3 ? now()->addMonths(rand(1, 3)) : null,
                'email_verified_at' => rand(0, 10) > 2 ? now() : null,
                'listings_free_count' => rand(0, 3),
            ];

            $users[] = User::create($userData);
        }

        return $users;
    }

    private function seedAuctions(array $users, array $categories): array
    {
        $auctions = [];
        $sellers = array_filter($users, fn ($u) => ! $u->is_admin);

        $statuses = [
            'active' => 0.60,    // 60% aktywnych
            'ended' => 0.25,     // 25% zakończonych
            'cancelled' => 0.05, // 5% anulowanych
            'pending' => 0.10,   // 10% oczekujących
        ];

        $currentYear = date('Y');

        for ($i = 1; $i <= 250; $i++) {
            $seller = $sellers[array_rand($sellers)];
            $category = $categories[array_rand($categories)];
            $breed = $this->pigeonBreeds[array_rand($this->pigeonBreeds)];
            $color = $this->pigeonColors[array_rand($this->pigeonColors)];
            $year = rand($currentYear - 3, $currentYear);
            $status = $this->getWeightedRandomStatus($statuses);
            $type = 'buy_now';

            $titleTemplate = $this->auctionTitles[array_rand($this->auctionTitles)];
            $title = str_replace(
                ['{{breed}}', '{{year}}', '{{color}}'],
                [$breed, $year, $color],
                $titleTemplate
            );

            $descriptions = [
                "Oferuję wspaniałego gołębia z renomowanej hodowli. Rodzice to sprawdzone asy, wielokrotnie nagradzane w konkursach krajowych i międzynarodowych. Gołąb jest w doskonałej kondycji, zdrowy, szczepiony i gotowy do startów.\n\nRodzice:\n- Ojciec: Champion lotu długodystansowego 2022\n- Matka: Córka olimpijskiego zwycięzcy\n\nGołąb posiada pełną dokumentację i obrączki PZHGP.",

                "Sprzedam perspektywicznego gołębia z linii belgijskiej, znany rodowód, świetne geny. Gołąb jest aktywny, zdrowy, regularnie latający. Idealne dla hodowców szukających świeżej krwi do hodowli.\n\nOsiągnięcia rodziców:\n- 5x Top 10 w lotach krajowych\n- 2x Top 100 w lotach międzynarodowych\n\nOkazja dla poważnych hodowców. Możliwość odbioru osobistego lub wysyłka kurierem.",

                "Młody gołąb z certyfikowanej hodowli premium. Linia rodowodowa sięga najlepszych belgijskich i holenderskich hodowli. Gołąb był regularnie treningowany, zna trasę powrotną, lata pewnie.\n\nCechy:\n- Doskonała orientacja przestrzenna\n- Silna muskulatura\n- Zdrowe, lśniące pióra\n- Idealna postawa\n\nPełna dokumentacja hodowlana dołączona do aukcji.",
            ];

            $startPrice = match ($type) {
                'auction' => rand(100, 500) * 10, // 100-5000zł (step 10)
                'buy_now' => rand(150, 800) * 10, // 1500-8000zł (step 10)
            };

            $currentPrice = $startPrice;

            if ($status === 'ended') {
                $currentPrice = $startPrice * rand(10, 25) / 10; // 1.0x - 2.5x wzrost
            }

            $startsAt = match ($status) {
                'active', 'ended' => now()->subDays(rand(1, 14)),
                'pending' => now()->addDays(rand(1, 5)),
                'cancelled' => now()->subDays(rand(1, 30)),
            };

            $endsAt = match ($status) {
                'active' => now()->addHours(rand(1, 168)), // 1h - 7 dni
                'ended' => now()->subHours(rand(1, 720)), // zakończone w ciągu miesiąca
                'pending' => $startsAt->copy()->addDays(7),
                'cancelled' => $startsAt->copy()->addDays(3),
            };

            $auction = Auction::create([
                'user_id' => $seller->id,
                'category_id' => $category->id,
                'title' => $title,
                'description' => $descriptions[array_rand($descriptions)],
                'type' => $type,
                'breed' => $breed,
                'year' => $year,
                'gender' => ['samiec', 'samica', 'golab_mlody'][array_rand([0, 1, 2])],
                'color' => $color,
                'size' => ['maly', 'sredni', 'duzy'][array_rand([0, 1, 2])],
                'ring_number' => 'PL-'.$year.'-'.rand(10000, 99999),
                'start_price' => $startPrice,
                'current_price' => $currentPrice,
                'winner_id' => $status === 'ended' ? $sellers[array_rand($sellers)]->id : null,
                'started_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => $status,
                'pigeon_images' => $this->generateRandomPigeonImages(),
                'anti_sniper_enabled' => rand(0, 10) < 8, // 80% ma anti-sniper
                'sniper_threshold' => 2,
                'extension_minutes' => 5,
            ]);

            $auctions[] = $auction;
        }

        return $auctions;
    }

    private function seedBids(array $auctions, array $users): void
    {
        $activeAuctions = array_filter($auctions, fn ($a) => $a->status === 'active');
        $endedAuctions = array_filter($auctions, fn ($a) => $a->status === 'ended');
        $bidders = array_filter($users, fn ($u) => ! $u->is_admin);

        foreach ($activeAuctions as $auction) {
            if ($auction->type !== 'auction') {
                continue;
            }

            $bidsCount = rand(0, 25);
            $currentPrice = $auction->start_price;

            for ($i = 0; $i < $bidsCount; $i++) {
                $bidder = $bidders[array_rand($bidders)];
                $bidAmount = $currentPrice + rand(10, 100) * 10;

                Bid::create([
                    'auction_id' => $auction->id,
                    'user_id' => $bidder->id,
                    'amount' => $bidAmount,
                    'is_auto_bid' => rand(0, 10) < 3, // 30% automatycznych
                    'max_auto_bid' => rand(0, 10) < 3 ? $bidAmount + rand(100, 500) * 10 : null,
                    'created_at' => $auction->started_at->copy()->addMinutes(rand(5, 10000)),
                ]);

                $currentPrice = $bidAmount;
            }

            if ($bidsCount > 0) {
                $auction->update(['current_price' => $currentPrice]);
            }
        }

        foreach ($endedAuctions as $auction) {
            if ($auction->type !== 'auction' || ! $auction->winner_id) {
                continue;
            }

            $bidsCount = rand(3, 40);
            $currentPrice = $auction->start_price;

            for ($i = 0; $i < $bidsCount; $i++) {
                $bidder = $i === $bidsCount - 1 ? $auction->winner_id : $bidders[array_rand($bidders)]->id;
                $bidAmount = $currentPrice + rand(10, 150) * 10;

                Bid::create([
                    'auction_id' => $auction->id,
                    'user_id' => $bidder,
                    'amount' => $bidAmount,
                    'is_auto_bid' => rand(0, 10) < 2,
                    'created_at' => $auction->started_at->copy()->addMinutes(rand(5, 10000)),
                ]);

                $currentPrice = $bidAmount;
            }
        }
    }

    private function seedReviews(array $users): void
    {
        $endedAuctions = Auction::where('status', 'ended')
            ->whereNotNull('winner_id')
            ->with(['user', 'winner'])
            ->get();

        foreach ($endedAuctions as $auction) {
            if (rand(0, 10) < 7 && $auction->winner_id && $auction->user_id !== $auction->winner_id) {
                $rating = rand(3, 5);

                $comments = [
                    5 => [
                        'Świetny hodowca! Gołąb zgodny z opisem, doskonała komunikacja. Polecam!',
                        'Najwyższa jakość! Super gołębie, profesjonalna obsługa. Na pewno wrócę!',
                        'Bardzo zadowolony z zakupu. Gołąb jest wspaniały, wszystko jak opisane.',
                        'Wyśmienita transakcja, szybka wysyłka, gołąb zdrowy i piękny. Dziękuję!',
                        'TOP hodowca! Polecam każdemu, świetne gołębie i fachowa pomoc.',
                    ],
                    4 => [
                        'Bardzo dobry hodowca, gołąb jest jak w opisie. Polecam.',
                        'Wszystko w porządku, gołąb zdrowy, wysyłka sprawna.',
                        'Dobra transakcja, gołąb piękny, bez zastrzeżeń.',
                        'Polecam, solidny hodowca, drobne opóźnienie w wysyłce.',
                    ],
                    3 => [
                        'Gołąb zgodny z opisem, wysyłka trochę za długo trwała.',
                        'OK, wszystko zgodne z umową, brak większych zastrzeżeń.',
                        'Średnio, gołąb dobry ale komunikacja mogłaby być lepsza.',
                    ],
                ];

                Review::create([
                    'from_user_id' => $auction->winner_id,
                    'to_user_id' => $auction->user_id,
                    'auction_id' => $auction->id,
                    'rating' => $rating,
                    'comment' => $comments[$rating][array_rand($comments[$rating])],
                    'created_at' => $auction->ends_at->copy()->addDays(rand(1, 14)), // recenzja 1-14 dni po zakończeniu
                ]);
            }
        }
    }

    private function generateRandomPigeonImages(): array
    {
        $count = rand(1, 5);
        $images = [];

        for ($i = 0; $i < $count; $i++) {
            $images[] = 'https://picsum.photos/seed/'.Str::random(10).'/800/600';
        }

        return $images;
    }

    private function getWeightedRandomStatus(array $weights): string
    {
        $rand = mt_rand(1, 100) / 100;
        $sum = 0;

        foreach ($weights as $status => $weight) {
            $sum += $weight;
            if ($rand <= $sum) {
                return $status;
            }
        }

        return 'active';
    }

    private function printSummary(array $users, array $auctions): void
    {
        $activeCount = count(array_filter($auctions, fn ($a) => $a->status === 'active'));
        $endedCount = count(array_filter($auctions, fn ($a) => $a->status === 'ended'));
        $buyNowCount = count(array_filter($auctions, fn ($a) => $a->type === 'buy_now'));
        $premiumUsers = count(array_filter($users, fn ($u) => $u->is_premium));

        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('📊 PODSUMOWANIE SEEDOWANIA');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('👥 Użytkownicy: '.count($users)." (Premium: {$premiumUsers})");
        $this->command->info('🏆 Aukcje: '.count($auctions));
        $this->command->info("   ├─ Aktywne: {$activeCount}");
        $this->command->info("   ├─ Zakończone: {$endedCount}");
        $this->command->info("   └─ Kup teraz: {$buyNowCount}");
        $this->command->info('💰 Licytacje: ~'.Bid::count());
        $this->command->info('⭐ Recenzje: '.Review::count());
        $this->command->info('📂 Kategorie: '.Category::count());
        $this->command->info('═══════════════════════════════════════');
        $this->command->newLine();
        $this->command->info('🔑 Konta testowe:');
        $this->command->info('   Admin: '.config('platform.admin.email'));
        $this->command->info('   Hodowca: jankowalski1@hodowla.pl');
        $this->command->info('   User: anna.nowak21@example.com');
        $this->command->newLine();
    }
}
