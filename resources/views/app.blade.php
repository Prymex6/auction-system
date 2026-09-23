<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $seoTitle = $seo['title'] ?? 'Gołębiowy Lot — aukcje gołębi pocztowych';
        $seoDescription = $seo['description'] ?? 'Elitarna giełda i platforma aukcyjna dla hodowców gołębi pocztowych. Sprawdzone pochodzenie, uczciwe aukcje.';
        $seoKeywords = $seo['keywords'] ?? 'aukcje gołębi, giełda gołębi, gołębie pocztowe, hodowla, rynek, licytacja, sprzedaż';
        $seoImage = $seo['image'] ?? url('/images/logo-golab.png');
        $seoUrl = $seo['url'] ?? url()->current();
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <link rel="canonical" href="{{ $seoUrl }}">

    <meta property="og:type" content="{{ ($seo['type'] ?? null) === 'article' ? 'article' : 'website' }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:url" content="{{ $seoUrl }}">
    <meta property="og:site_name" content="Gołębiowy Lot">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">

    <link rel="icon" type="image/png" href="/images/logo-golab.png">

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563eb">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Gołębiowy Lot">

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script type="application/ld+json">
    {!! json_encode([
        '@@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => config('app.name'),
        'description' => 'Elitarna giełda i platforma aukcyjna dla hodowców gołębi pocztowych.',
        'url' => url('/'),
        'image' => url('/images/logo-golab.png'),
        'telephone' => config('platform.contact.phone'),
        'email' => config('platform.contact.email'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => config('platform.operator.address'),
            'postalCode' => config('platform.operator.postal_code'),
            'addressLocality' => config('platform.operator.city'),
            'addressCountry' => 'PL',
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    @if(($seo['type'] ?? null) === 'auction')
    <script type="application/ld+json">
    {!! json_encode([
        '@@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $seoTitle,
        'description' => $seoDescription,
        'image' => $seoImage,
        'offers' => [
            '@type' => 'Offer',
            'url' => $seoUrl,
            'priceCurrency' => 'PLN',
            'price' => $seo['price'] ?? 0,
            'availability' => 'https://schema.org/' . ($seo['availability'] ?? 'InStock'),
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @endif

    @if(($seo['type'] ?? null) === 'article')
    <script type="application/ld+json">
    {!! json_encode([
        '@@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $seoTitle,
        'description' => $seoDescription,
        'image' => $seoImage,
        'datePublished' => $seo['published_at'] ?? null,
        'author' => ['@type' => 'Organization', 'name' => 'Gołębiowy Lot'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @endif

    @if(($seo['type'] ?? null) === 'auction' || ($seo['type'] ?? null) === 'article')
    <script type="application/ld+json">
    {!! json_encode([
        '@@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Strona główna', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => ($seo['type'] ?? null) === 'auction' ? 'Aukcje' : 'Blog', 'item' => url(($seo['type'] ?? null) === 'auction' ? '/auctions' : '/blog')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $seo['title'] ?? $seoTitle, 'item' => $seoUrl],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div id="app"></div>
</body>
</html>
