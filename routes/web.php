<?php

use App\Models\Auction;
use App\Models\BlogPost;
use App\Models\NewsletterSubscriber;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id) {
    $user = User::find($id);

    if (! $user || ! hash_equals(sha1($user->email), (string) $request->route('hash'))) {
        return redirect(config('app.url').'/login?verified=invalid');
    }

    if (! $request->hasValidSignature()) {
        return redirect(config('app.url').'/login?verified=expired');
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    return redirect(config('app.url').'/login?verified=1');
})->name('verification.verify');

Route::get('/newsletter/unsubscribe/{email}', function (Request $request, $email) {
    if (! $request->hasValidSignature()) {
        return response()->view('emails.newsletter-unsubscribe-result', [
            'success' => false,
        ], 403);
    }

    NewsletterSubscriber::where('email', mb_strtolower(trim($email)))->delete();

    return response()->view('emails.newsletter-unsubscribe-result', [
        'success' => true,
        'email' => $email,
    ]);
})->name('newsletter.unsubscribe');

Route::get('/sitemap.xml', function () {
    $urls = [];

    $static = ['/', '/auctions', '/blog', '/how-it-works', '/terms', '/privacy', '/cookies', '/help', '/contact', '/faq', '/security'];
    foreach ($static as $path) {
        $urls[] = ['loc' => url($path), 'changefreq' => 'daily', 'priority' => $path === '/' ? '1.0' : '0.6'];
    }

    Auction::where('status', 'active')->select('id', 'updated_at')->get()->each(function ($auction) use (&$urls) {
        $urls[] = [
            'loc' => url('/auctions/'.$auction->id),
            'lastmod' => $auction->updated_at->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.8',
        ];
    });

    BlogPost::published()->select('slug', 'updated_at')->get()->each(function ($post) use (&$urls) {
        $urls[] = [
            'loc' => url('/blog/'.$post->slug),
            'lastmod' => $post->updated_at->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.5',
        ];
    });

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    foreach ($urls as $u) {
        $xml .= '<url><loc>'.e($u['loc']).'</loc>';
        if (isset($u['lastmod'])) {
            $xml .= '<lastmod>'.$u['lastmod'].'</lastmod>';
        }
        $xml .= '<changefreq>'.$u['changefreq'].'</changefreq>';
        $xml .= '<priority>'.$u['priority'].'</priority>';
        $xml .= '</url>'."\n";
    }
    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml');
});

Route::get('/{any}', function (Request $request) {
    $seo = null;
    $platformSettings = PlatformSetting::first();

    if (preg_match('#^auctions/(\d+)$#', $request->path(), $m)) {
        $auction = Auction::find((int) $m[1]);
        if ($auction) {
            $image = $auction->pigeon_images[0] ?? null;
            $seo = [
                'type' => 'auction',
                'title' => $auction->title.' - Gołębiowy Lot',
                'description' => Str::limit(strip_tags($auction->description ?? ''), 160) ?: 'Zweryfikowana oferta na platformie aukcyjnej dla hodowców gołębi pocztowych.',
                'image' => $image ? url($image) : null,
                'url' => $request->url(),
                'price' => $auction->current_price ?? $auction->start_price,
                'availability' => $auction->status === 'active' ? 'InStock' : 'SoldOut',
            ];
        }
    } elseif ($request->path() === 'auctions') {
        $activeCount = Auction::where('status', 'active')->count();
        $seo = [
            'title' => "Aukcje i giełda gołębi pocztowych — {$activeCount} aktywnych ofert - Gołębiowy Lot",
            'description' => "Przeglądaj żywą giełdę i aukcje gołębi pocztowych sprawdzonych hodowców. Aktualnie {$activeCount} aktywnych ofert ze sprawdzonym rodowodem.",
            'url' => $request->url(),
        ];
    } elseif ($request->path() === 'blog') {
        $seo = [
            'title' => 'Blog o gołębiach pocztowych — porady, hodowla, rodowody - Gołębiowy Lot',
            'description' => 'Praktyczne poradniki o hodowli, wyborze i pielęgnacji gołębi pocztowych — sprawdzone porady od doświadczonych hodowców.',
            'url' => $request->url(),
        ];
    } elseif (preg_match('#^blog/([a-z0-9\-]+)$#', $request->path(), $m)) {
        $post = BlogPost::where('slug', $m[1])->first();
        if ($post) {
            $seo = [
                'type' => 'article',
                'title' => $post->title.' - Gołębiowy Lot',
                'description' => Str::limit(strip_tags($post->excerpt ?: ($post->content ?? '')), 160),
                'image' => $post->image ? url($post->image) : null,
                'url' => $request->url(),
                'published_at' => optional($post->published_at)->toIso8601String(),
            ];
        }
    }

    // zaszytych na sztywno w widoku.
    if (! $seo && ($platformSettings?->seo_title || $platformSettings?->seo_description)) {
        $seo = [
            'title' => $platformSettings->seo_title,
            'description' => $platformSettings->seo_description,
            'url' => $request->url(),
        ];
    }
    if ($platformSettings?->seo_keywords) {
        $seo = $seo ?? [];
        $seo['keywords'] = $platformSettings->seo_keywords;
    }

    return view('app', ['seo' => $seo]);
})->where('any', '.*');
