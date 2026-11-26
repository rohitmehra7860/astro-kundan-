<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Search Engine Verification -->
@if($googleVerification = \App\Models\Setting::get('google_site_verification'))
    <meta name="google-site-verification" content="{{ $googleVerification }}">
@endif
@if($bingVerification = \App\Models\Setting::get('bing_site_verification'))
    <meta name="msvalidate.01" content="{{ $bingVerification }}">
@endif

<!-- Primary SEO Metadata -->
<title>{{ $seo['meta_title'] ?? $page->title ?? \App\Models\Setting::get('site_name', 'Astro Kundan') }}</title>
<meta name="description" content="{{ $seo['meta_description'] ?? '' }}">
<meta name="keywords" content="{{ $seo['meta_keywords'] ?? '' }}">
<meta name="robots" content="{{ $seo['robots_instructions'] ?? 'index, follow' }}">
<link rel="canonical" href="{{ $seo['canonical_url'] ?? request()->url() }}">

<!-- Dynamic Site Favicon -->
@php
    $faviconUrl = null;
    if ($faviconId = \App\Models\Setting::get('favicon_id')) {
        $media = \App\Models\MediaUpload::find($faviconId);
        $faviconUrl = $media?->getFirstMediaUrl('gallery');
    }
@endphp
@if($faviconUrl)
    <link rel="icon" href="{{ $faviconUrl }}">
@else
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
@endif

<!-- Open Graph / Facebook -->
<meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}">
<meta property="og:locale" content="{{ $seo['og_locale'] ?? \App\Models\Setting::get('site_locale', 'en_US') }}">
<meta property="og:url" content="{{ $seo['canonical_url'] ?? request()->url() }}">
<meta property="og:title" content="{{ $seo['og_title'] ?? $seo['meta_title'] ?? $page->title }}">
<meta property="og:description" content="{{ $seo['og_description'] ?? $seo['meta_description'] ?? '' }}">
@if(!empty($seo['og_image_url']))
    <meta property="og:image" content="{{ $seo['og_image_url'] }}">
@endif
@if(!empty($seo['article_published_time']))
    <meta property="article:published_time" content="{{ $seo['article_published_time'] }}">
    <meta property="article:modified_time" content="{{ $seo['article_modified_time'] ?? $seo['article_published_time'] }}">
    @if(!empty($seo['article_section']))<meta property="article:section" content="{{ $seo['article_section'] }}">@endif
    @if(!empty($seo['article_author']))<meta property="article:author" content="{{ $seo['article_author'] }}">@endif
@endif

<!-- Twitter / X -->
<meta name="twitter:card" content="{{ $seo['twitter_card'] ?? 'summary_large_image' }}">
<meta name="twitter:url" content="{{ $seo['canonical_url'] ?? request()->url() }}">
<meta name="twitter:title" content="{{ $seo['twitter_title'] ?? $seo['meta_title'] ?? $page->title }}">
<meta name="twitter:description" content="{{ $seo['twitter_description'] ?? $seo['meta_description'] ?? '' }}">
@if(!empty($seo['twitter_image']))
    <meta name="twitter:image" content="{{ $seo['twitter_image'] }}">
@endif
@if(!empty($seo['twitter_site']))
    <meta name="twitter:site" content="{{ $seo['twitter_site'] }}">
@endif

<!-- Google Tag Manager (Head Script) -->
@if($gtmId = \App\Models\Setting::get('google_tag_manager_id'))
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ $gtmId }}');</script>
@endif

<!-- Google Analytics (GA4) -->
@if($gaId = \App\Models\Setting::get('google_analytics_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $gaId }}');
    </script>
@endif

<!-- Meta Pixel (Facebook Pixel) -->
@if($pixelId = \App\Models\Setting::get('meta_pixel_id'))
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ $pixelId }}');
    fbq('track', 'PageView');
    </script>
@endif

<!-- JSON-LD Structured Data Schemas -->
@if(!empty($seo['schema_json_ld']))
    <script type="application/ld+json">
        {!! json_encode($seo['schema_json_ld'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif
@if(!empty($seo['breadcrumb_json_ld']))
    <script type="application/ld+json">
        {!! json_encode($seo['breadcrumb_json_ld'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif
@if(!empty($seo['faq_json_ld']))
    <script type="application/ld+json">
        {!! json_encode($seo['faq_json_ld'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif
@if(!empty($seo['structured_schema_json_ld']))
    <script type="application/ld+json">
        {!! json_encode($seo['structured_schema_json_ld'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif

<!-- Custom Head Scripts from Site Settings -->
@if($customHead = \App\Models\Setting::get('custom_head_scripts'))
    {!! $customHead !!}
@endif

@vite(['resources/css/frontend.css', 'resources/js/frontend.js'])
