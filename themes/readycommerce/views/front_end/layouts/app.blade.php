@php
    $locale = app()->getLocale() ?? 'en';
    $favicon = $themeSettings['header_favicon'] ?? '';
    $faviconUrl = (!empty($favicon) && !str_contains($favicon, 'stylique') && is_file(public_path($favicon)))
        ? get_file($favicon)
        : asset('readycart/logo-mark.png');
    $assetTheme = $currentTheme ?? 'stylique';
    if (!file_exists(public_path('themes/' . $assetTheme . '/assets/css/all.min.css'))) {
        $assetTheme = 'stylique';
    }
    $storePrimaryColor = trim((string) ($themeSettings['store_primary_color'] ?? '#F05A00'));
    if (!preg_match('/\A#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})\z/', $storePrimaryColor) || strtolower($storePrimaryColor) === '#f95738' || strtolower($storePrimaryColor) === '#f8796c') {
        $storePrimaryColor = '#F05A00';
    }
    $primaryColorHex = ltrim($storePrimaryColor, '#');
    if (strlen($primaryColorHex) === 3) {
        $primaryColorHex = $primaryColorHex[0] . $primaryColorHex[0] . $primaryColorHex[1] . $primaryColorHex[1] . $primaryColorHex[2] . $primaryColorHex[2];
    }
    $primaryColorChannels = array_map('hexdec', str_split($primaryColorHex, 2));
    $primaryColorRgb = implode(' ', $primaryColorChannels);
    $primaryHoverColor = strtolower($storePrimaryColor) === '#f05a00'
        ? '#D94F00'
        : sprintf(
            '#%02x%02x%02x',
            (int) round($primaryColorChannels[0] * 0.88),
            (int) round($primaryColorChannels[1] * 0.88),
            (int) round($primaryColorChannels[2] * 0.88)
        );
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ in_array($locale, ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}" data-pwa-enabled="{{ ($store->enable_pwa_store ?? 'off') === 'on' ? 'true' : 'false' }}" data-store-id="{{ $store->id ?? '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="author" content="{{ config('app.name', $store->name ?? 'Ready eCommerce') }}">
    <meta name="base-url" content="{{ URL::to('/') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {!! metaKeywordSetting($metakeyword ?? null, $metadesc ?? null, $metaimage ?? null, $slug) !!}
    <title>@yield('page-title') Â· Ready eCommerce</title>
    <link rel="icon" href="{{ $faviconUrl }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @if (file_exists(public_path('themes/' . $assetTheme . '/assets/css/all.min.css')))
        <link rel="stylesheet" href="{{ asset('themes/' . $assetTheme . '/assets/css/all.min.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/select2.min.css') }}">
    @vite(['resources/css/readycommerce-storefront.css'])
    <style>:root { --rc-primary: {{ $storePrimaryColor }}; --rc-primary-hover: {{ $primaryHoverColor }}; --rc-primary-rgb: {{ $primaryColorRgb }}; }</style>
    <style>{!! $storecss ?? '' !!}</style>
    @stack('page-style')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/select2.min.js') }}"></script>
    @include('layouts.includes.turbo')
    @vite(['resources/js/readycommerce-storefront.js'])
</head>
<body class="rc-body min-h-screen bg-white text-ink font-sans antialiased">
    <div class="overlay hidden" id="filter-overlay"></div>
    <div class="min-h-screen bg-white pb-20 md:pb-0">
        @include('front_end.partison.header')
        @if (isset($pixelScript))
            @foreach ($pixelScript as $script)
                {!! $script !!}
            @endforeach
        @endif
        @yield('content')
        @include('front_end.partison.footer')
        @include('front_end.partison.mobile-bottom-nav')
    </div>
    @include('front_end.jQueryRoute')
    @if (file_exists(public_path('themes/' . $assetTheme . '/assets/js/swiper-bundle.min.js')))
        <script src="{{ asset('themes/' . $assetTheme . '/assets/js/swiper-bundle.min.js') }}"></script>
    @endif
    @if (file_exists(public_path('themes/' . $assetTheme . '/assets/js/custom.js')))
        <script src="{{ asset_v('themes/' . $assetTheme . '/assets/js/custom.js') }}"></script>
    @endif
    @if ($message = Session::get('success'))
        <script>if (window.notifier) window.notifier.show('Success', @json($message), 'success', '{{ asset('public/assets/images/notification/ok-48.png') }}', 4000);</script>
    @endif
    @if ($message = Session::get('error'))
        <script>if (window.notifier) window.notifier.show('Error', @json($message), 'danger', '{{ asset('public/assets/images/notification/high_priority-48.png') }}', 4000);</script>
    @endif
    @include('front_end.hooks.footer_link')
    @stack('page-script')
    <script>
        window.show_toastr = function (title, message, type) {
            if (window.rcShowToast) {
                window.rcShowToast(message || title);
            }
            if (!window.notifier) return;
            const success = type === 'success';
            const icon = success ? '{{ asset('public/assets/images/notification/ok-48.png') }}' : '{{ asset('public/assets/images/notification/high_priority-48.png') }}';
            window.notifier.show(title || (success ? 'Success' : 'Error'), message, success ? 'success' : 'danger', icon, 4000);
        };
    </script>
    @include('front_end.partison.pwa-install')
</body>
</html>

