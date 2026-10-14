<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) ?? 'en' }}" dir="{{ ($currantLang == 'ar' || $currantLang == 'he') ? 'rtl' : 'ltr' }}" data-pwa-enabled="{{ $store->enable_pwa_store == 'on' ? 'true' : 'false' }}" data-store-id="{{ $store->id ?? '' }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  @if (env('MOBILE_APP_FEEL', true))
    @vite(['resources/css/mobile-app-feel.css'])
  @endif
  <meta name="author" content="{{ config('app.name') ?? Stylique }}">
  <meta name="base-url" content="{{ URL::to('/') }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
   {!! metaKeywordSetting($metakeyword ?? null,$metadesc ?? null,$metaimage ?? null,$slug) !!}
  <title>@yield('page-title')</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">
  
  {{-- Demo Site Typography (CLAUDE-DEMO). Kill-switch: DEMO_THEME_ENABLED=false --}}
  @if (env('DEMO_THEME_ENABLED', false))
    <link
      href="https://fonts.googleapis.com/css2?family=Satoshi:wght@300;400;500;700;900&display=swap"
      rel="stylesheet">
  @endif
  
  <link rel="stylesheet" href="{{ asset('themes/' . $currentTheme . '/assets/css/all.min.css') }}">
  
  @if (env('LEGACY_TAILWIND_CDN', false))
    {{-- Kill-switch: Use legacy Play CDN for Tailwind CSS generation --}}
    <link rel="stylesheet" href="{{ asset('themes/' . $currentTheme . '/assets/css/swiper-bundle.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('themes/' . $currentTheme . '/assets/css/main-style.css') }}">
    <script src="{{ asset('themes/' . $currentTheme . '/assets/js/tailwind.js') }}"></script>
  @else
    {{-- Use Vite-compiled CSS bundle (production default) --}}
    @vite(['resources/css/store.css'])
  @endif
 
    <meta name="mobile-wep-app-capable" content="yes">
    <meta name="apple-mobile-wep-app-capable" content="yes">
    <meta name="msapplication-starturl" content="/">
    <!-- Favicon icon -->
    @php
        $favicon = $themeSettings['header_favicon'] ?? '';
        $faviconUrl = !empty($favicon) ? get_file($favicon) : asset('storage/uploads/logo/favicon.png');
    @endphp
    <link rel="icon" href="{{ $faviconUrl }}" type="image/x-icon" />
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" sizes="167x167" href="{{ $faviconUrl }}">
     @if ($store->enable_pwa_store == 'on')
         <link rel="manifest" href="{{ route('pwa.manifest', $store->slug) }}" />
         <meta name="theme-color" content="{{ $themeSettings['header_color'] ?? '#000000' }}" />
         <meta name="apple-mobile-web-app-capable" content="yes" />
         <meta name="apple-mobile-web-app-status-bar-style" content="default" />
         <meta name="apple-mobile-web-app-title" content="{{ substr($store->name, 0, 12) }}" />
     @endif
     <style>
         {!! $storecss ?? '' !!}
     </style>
     @stack('page-style')
     
     {{-- Demo Theme Styling (CLAUDE-DEMO Phase 5) --}}
     @if (env('DEMO_THEME_ENABLED', false))
       @include('components.header-demo-style')
       @include('components.footer-demo-style')
     @endif

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    @if($currantLang == 'ar' || $currantLang == 'he')
        <link rel="stylesheet" href="{{ asset('assets/css/rtl-floating-wpp.min.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/floating-wpp.min.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/select2.min.css') }}">
    <script src="{{ asset('assets/js/plugins/select2.min.js') }}"></script>

    {{--
        Turbo Drive (CLAUDE-5). Must stay in <head>: Turbo warns when loaded from
        <body>, and the onTurboLoad/onTurboOnce stub inside has to be defined
        before the first inline script in <body> calls it.
        Kill-switch: TURBO_ENABLED=false in .env.
    --}}
    @include('layouts.includes.turbo')
</head>

<body>
    <div class="overlay" id="filter-overlay"></div>
   @include('front_end.partison.header')
    @if(isset($pixelScript))
        @foreach ($pixelScript as $script)
            <?= $script ?>
        @endforeach
    @endif

    @yield('content')
    @include('front_end.partison.footer')

    @include('front_end.jQueryRoute')
  <!-- Swiper JS -->

  <script src="{{ asset('themes/' . $currentTheme . '/assets/js/swiper-bundle.min.js') }}"></script>

  {{--
      asset_v() rather than asset() (CLAUDE-6). This file was edited to wrap its
      29 top-level `const` declarations in an IIFE, without which every Turbo
      navigation throws "Identifier 'searchToggle' has already been declared" and
      kills every storefront widget.

      The .htaccess Cache-Control rule from CLAUDE-3 serves .js with
      `max-age=604800`, and this URL carried no version at all — so returning
      visitors would have kept the broken copy for up to seven days, with no way
      for us to invalidate it. asset_v() appends the file's mtime, so the URL
      changes exactly when the file does.
  --}}
  <script src="{{ asset_v('themes/' . $currentTheme . '/assets/js/custom.js') }}"></script>

   <!--Theme Routes Script-->
    
    <!--scripts end here-->
   @if ($message = Session::get('success'))
        <script>
            var site_url = $('meta[name="base-url"]').attr('content');
            notifier.show('Success', '{!! $message !!}', 'success', site_url +
                '/public/assets/images/notification/ok-48.png', 4000);
        </script>
    @endif

    @if ($message = Session::get('error'))
        <script>
            var site_url = $('meta[name="base-url"]').attr('content');
            notifier.show('Error', '{!! $message !!}', 'danger', site_url +
                '/public/assets/images/notification/high_priority-48.png', 4000);
        </script>
    @endif
    @include('front_end.hooks.footer_link')
    @stack('page-script')
    <script>

        function show_toastr(title, message, type) {
            var o, i;
            var icon = '';
            var cls = '';
            if (type == 'success') {
                cls = 'primary';
                notifier.show('Success', message, 'success', site_url + '/public/assets/images/notification/ok-48.png', 4000);
            } else {
                cls = 'danger';
                notifier.show('Error', message, 'danger', site_url + '/public/assets/images/notification/high_priority-48.png', 4000);
            }
        }
    </script>

    {{-- Mobile bottom navigation (BULK-6) --}}
    @include('front_end.partison.mobile-bottom-nav')

    {{-- PWA install banner (BULK-5 / CLAUDE-11) --}}
    @include('front_end.partison.pwa-install')
</body>
</html>
