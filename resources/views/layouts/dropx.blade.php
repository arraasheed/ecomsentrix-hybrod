@php
    $store_settings = \App\Models\Utility::getStoreSettings();
    $name = isset($store_settings['name']) ? $store_settings['name'] : config('app.name', 'eCommerceGo');
    $color = isset($store_settings['theme_color']) ? $store_settings['theme_color'] : 'info';
    $meta_image = get_file(isset($store_settings['meta_image']) ? $store_settings['meta_image'] : '');
    $currantLang = basename(App::getLocale());
    $languages = \App\Models\Utility::languages();
    $settings = \App\Models\Utility::settings();
    $user = \Auth::user();
    $ownerId = \App\Models\Utility::ownerId();
    $setting = \App\Models\Utility::settingsById($ownerId);
    $lang = app()->getLocale();
    if ($lang == 'ar' || $lang == 'he') {
        $setting['SITE_RTL'] = 'on';
    }
    $dark_mode = isset($setting['dark_mode']) ? $setting['dark_mode'] : 'off';
    
    // Check DropX UI Kill-switch
    $dropx_enabled = env('DROXP_UI_ENABLED', true);
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ isset($setting['SITE_RTL']) && $setting['SITE_RTL'] == 'on' ? 'rtl' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ucwords($name) }} - @yield('page-title')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (Vite / CDN Fallback) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    
    @stack('css-page')
</head>
<body class="font-sans bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased pb-16 md:pb-0">

@if($dropx_enabled)
    <!-- DropX & Ecom Sentrix Layout -->
    @include('layouts.includes.ecomsentrix-sidebar')

    <!-- Sidebar overlay for mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/50 opacity-0 invisible transition-opacity duration-300 lg:hidden" data-sidebar-overlay="true"></div>

    <div class="main-content-wrapper min-h-screen flex flex-col justify-between">
        <div>
            @include('layouts.includes.ecomsentrix-header')
            
            <main class="p-4 md:p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-medium flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm font-medium flex items-center justify-between">
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

        <footer class="py-4 px-6 border-t border-slate-200 dark:border-slate-800 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} {{ $name }} &bull; Powered by Ecom Sentrix & DropX Architecture.
        </footer>
    </div>

    @include('layouts.includes.ecomsentrix-pwa-nav')
@else
    <!-- Fallback Standard Layout -->
    <div class="min-h-screen flex flex-col items-center justify-center p-6 text-center">
        <h1 class="text-2xl font-bold mb-2">DropX UI is disabled via environment kill-switch.</h1>
        <p class="text-slate-500 mb-4">Set <code class="bg-slate-200 px-2 py-1 rounded">DROXP_UI_ENABLED=true</code> in your .env file to enable.</p>
        <div>@yield('content')</div>
    </div>
@endif

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
@stack('script-page')
</body>
</html>
