@php
    use App\Support\WebsiteSettings;

    $seo = WebsiteSettings::seo();
    $branding = $websiteBranding ?? WebsiteSettings::branding();
    $pageTitle = $title ?? null;
    $documentTitle = $pageTitle
        ? $pageTitle.' — '.($branding['site_name'] ?? config('app.name'))
        : ($seo['meta_title'] !== '' ? $seo['meta_title'] : ($branding['site_name'] ?? config('app.name')));
@endphp

<title>{{ $documentTitle }}</title>

@if ($seo['meta_description'] !== '')
    <meta name="description" content="{{ $seo['meta_description'] }}">
@endif

@if ($seo['meta_keywords'] !== '')
    <meta name="keywords" content="{{ $seo['meta_keywords'] }}">
@endif

@if ($seo['robots_noindex'])
    <meta name="robots" content="noindex, nofollow">
@endif

<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $branding['site_name'] ?? config('app.name') }}">
<meta property="og:title" content="{{ $seo['og_title'] !== '' ? $seo['og_title'] : $documentTitle }}">
@if ($seo['og_description'] !== '')
    <meta property="og:description" content="{{ $seo['og_description'] }}">
@elseif ($seo['meta_description'] !== '')
    <meta property="og:description" content="{{ $seo['meta_description'] }}">
@endif
@if (! empty($branding['site_logo_url']))
    <meta property="og:image" content="{{ $branding['site_logo_url'] }}">
@endif
