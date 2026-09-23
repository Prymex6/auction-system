<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\Category;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Enough data to open the site and see it working: platform settings, one
 * category, an administrator and a handful of listings.
 *
 * Everything here is invented. Ring numbers, lofts and pedigrees name nobody
 * real, and the administrator comes from the environment, so a fresh clone
 * carries no personal data of anyone.
 */
class SampleAuctionsSeeder extends Seeder
{
    public function run(): void
    {
        PlatformSetting::updateOrCreate([], [
            'platform_name' => config('app.name'),
            'platform_email' => config('platform.contact.email'),
            'platform_description' => 'Giełda i platforma aukcyjna dla hodowców gołębi pocztowych.',
            'bidding_enabled' => false,
            'seo_title' => config('app.name').' — aukcje gołębi pocztowych',
            'seo_description' => 'Giełda i platforma aukcyjna dla hodowców gołębi pocztowych. Sprawdzone rodowody, uczciwe aukcje.',
            'seo_keywords' => 'aukcje gołębi,giełda gołębi,gołębie pocztowe,hodowla,licytacja,sprzedaż',
        ]);

        $category = Category::firstOrCreate(
            ['slug' => 'golebie-pocztowe'],
            [
                'name' => 'Gołębie pocztowe',
                'description' => 'Gołębie wyścigowe długo- i krótkodystansowe.',
                'category_type' => 'standard',
                'is_featured' => true,
            ]
        );

        $admin = $this->administrator();
        if ($admin === null) {
            $this->command->warn('SEED_ADMIN_PASSWORD is not set, so no administrator was created.');

            return;
        }

        foreach ($this->listings() as $listing) {
            Auction::updateOrCreate(
                ['ring_number' => $listing['ring_number']],
                [
                    'user_id' => $admin->id,
                    'category_id' => $category->id,
                    'title' => $listing['title'],
                    'description' => $listing['description'],
                    'breed' => $listing['breed'],
                    'year' => 2026,
                    'gender' => 'golab_mlody',
                    'color' => $listing['color'],
                    'size' => 'sredni',
                    'pigeon_images' => [$listing['photo']],
                    'type' => 'buy_now',
                    'start_price' => $listing['price'],
                    'current_price' => $listing['price'],
                    'started_at' => now(),
                    'ends_at' => now()->addYear(),
                    'status' => 'active',
                ]
            );
        }

        $this->command->info('Sample listings created for '.$admin->email);
    }

    /**
     * The administrator every instance needs to start with. Without a password
     * in the environment nothing is created: a seeded account with a known
     * password is how a demo becomes an incident.
     */
    private function administrator(): ?User
    {
        $password = config('platform.admin.password');
        if (blank($password)) {
            return null;
        }

        $details = [
            'email' => config('platform.admin.email'),
            'name' => config('platform.admin.name'),
            'password' => bcrypt($password),
            'is_admin' => true,
            'is_active' => true,
            'is_premium' => true,
            'email_verified_at' => now(),
        ];

        if (Schema::hasColumn('users', 'phone_verified_at')) {
            $details['phone_verified_at'] = now();
        }

        $existing = User::where('is_admin', true)->first();
        if ($existing) {
            $existing->update($details);

            return $existing;
        }

        return User::create($details);
    }

    /**
     * @return list<array{ring_number: string, breed: string, color: string, title: string, description: string, price: int, photo: string}>
     */
    private function listings(): array
    {
        return [
            [
                'ring_number' => 'PL-0000-26-0001',
                'photo' => '/images/pigeons/pigeon-06.jpg',
                'breed' => 'Linia Dalekodystansowa',
                'color' => 'Niebieski',
                'title' => 'Młody gołąb — linia dalekodystansowa (PL-0000-26-0001)',
                'description' => 'Młody gołąb z pary rodzicielskiej o udokumentowanych wynikach na dystansach powyżej 500 km. Pełny czteropokoleniowy rodowód w załączonym pliku.',
                'price' => 500,
            ],
            [
                'ring_number' => 'PL-0000-26-0002',
                'photo' => '/images/pigeons/pigeon-15.jpg',
                'breed' => 'Linia Dalekodystansowa',
                'color' => 'Niebieski',
                'title' => 'Młody gołąb — linia dalekodystansowa (PL-0000-26-0002)',
                'description' => 'Rodzeństwo PL-0000-26-0001 z tej samej pary rodzicielskiej. Pełny czteropokoleniowy rodowód w załączonym pliku.',
                'price' => 500,
            ],
            [
                'ring_number' => 'PL-0000-26-0003',
                'photo' => '/images/pigeons/pigeon-08.jpg',
                'breed' => 'Linia Sprinterska',
                'color' => 'Nakrapiana',
                'title' => 'Młody gołąb — linia sprinterska (PL-0000-26-0003)',
                'description' => 'Młody gołąb z linii nastawionej na loty do 300 km. W rodowodzie ptaki z czołowymi lokatami w lotach okręgowych. Rodowód trzypokoleniowy w załączonym pliku.',
                'price' => 650,
            ],
            [
                'ring_number' => 'PL-0000-26-0004',
                'photo' => '/images/pigeons/pigeon-16.jpg',
                'breed' => 'Linia Wsobna',
                'color' => 'Czarny',
                'title' => 'Młody gołąb — linia wsobna (PL-0000-26-0004)',
                'description' => 'Młody gołąb z silnie wsobnej linii hodowlanej, oboje rodzice z tego samego gniazda. Przeznaczony do rozpłodu. Pełny rodowód w załączonym pliku.',
                'price' => 900,
            ],
            [
                'ring_number' => 'PL-0000-26-0005',
                'photo' => '/images/pigeons/pigeon-14.jpg',
                'breed' => 'Linia Mieszana',
                'color' => 'Pstry',
                'title' => 'Młody gołąb — krzyżówka dwóch linii (PL-0000-26-0005)',
                'description' => 'Krzyżówka linii sprinterskiej i dalekodystansowej, z pary dobranej pod loty średniodystansowe. Rodowód trzypokoleniowy w załączonym pliku.',
                'price' => 450,
            ],
        ];
    }
}
