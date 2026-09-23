<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\PlatformSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetaTagsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_uses_seo_settings_when_configured()
    {
        PlatformSetting::updateOrCreate([], [
            'seo_title' => 'Tytul testowy SEO',
            'seo_description' => 'Opis testowy SEO dla strony glownej.',
            'seo_keywords' => 'test, seo, slowa-kluczowe',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Tytul testowy SEO', false);
        $response->assertSee('Opis testowy SEO dla strony glownej.', false);
        $response->assertSee('test, seo, slowa-kluczowe', false);
    }

    public function test_homepage_falls_back_to_defaults_when_seo_settings_empty()
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Gołębiowy Lot', false);
    }

    public function test_auction_page_keeps_its_own_specific_meta_regardless_of_seo_settings()
    {
        PlatformSetting::updateOrCreate([], [
            'seo_title' => 'Powinno nie byc widoczne na stronie aukcji',
        ]);
        $auction = Auction::factory()->create(['title' => 'Wyjatkowy tytul aukcji testowej']);

        $response = $this->get("/auctions/{$auction->id}");

        $response->assertOk();
        $response->assertSee('Wyjatkowy tytul aukcji testowej', false);
        $response->assertDontSee('Powinno nie byc widoczne na stronie aukcji', false);
    }
}
