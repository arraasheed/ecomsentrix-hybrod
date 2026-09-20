@php
    if(auth()->user() && auth()->user()->type == 'admin') {
        $setting = getAdminAllSetting();
        $supperadminSetting = \App\Models\Setting::where('name', 'disable_lang')->where('created_by', 1)->pluck('value', 'name')->toArray();
        $setting['disable_lang'] = $supperadminSetting['disable_lang'] ?? null;
    } else {
        $setting = getSuperAdminAllSetting();
    }
    $SuperadminData = getSuperAdminAllSetting();
    $cust_darklayout = \App\Models\Utility::GetValueByName('cust_darklayout');
    if ($cust_darklayout == '') {
        $setting['cust_darklayout'] = 'off';
    }

    $cust_theme_bg = \App\Models\Utility::GetValueByName('cust_theme_bg');
    if($cust_theme_bg == ''){
        $setting['cust_theme_bg'] = 'on';
    }

    $SITE_RTL = \App\Models\Utility::GetValueByName('SITE_RTL');
    if($SITE_RTL == ''){
        $setting['SITE_RTL'] = 'off';
    }

    if (!isset($setting['color'])) {
        $themeColor = 'theme-3';
        $color = 'theme-3';
    } elseif (isset($setting['color_flag']) && $setting['color_flag'] == 'true') {
        $themeColor = 'custom-color';
        $color = $setting['color'];
    } else {
        if (!in_array($setting['color'], ['theme-1','theme-2','theme-3','theme-4','theme-5','theme-6','theme-7','theme-8','theme-9','theme-10'])) {
            $themeColor = 'custom-color' ?? 'theme-3';
            $color = $setting['color'];
        } else {
            $themeColor = $setting['color'] ?? 'theme-3';
            $color = $setting['color'];
        }

    }

    if(auth()->user() && auth()->user()->language) {
        $setting['currantLang'] = auth()->user()->language;
    } else {
        $setting['currantLang'] = 'en';
    }

    if ($setting['currantLang'] == 'ar' || $setting['currantLang'] == 'he') {
        $setting['SITE_RTL'] = 'on';
    }

    // Compute admin CSS variant based on RTL + dark layout settings (P3 optimization).
    // The four variants are registered in vite.config.js and built from resources/css/admin-*.css.
    // This matches the conditional block below (lines 102-118) exactly, and $adminCssVariant
    // is used in the @vite() call to pick the correct variant bundle.
    $isRTL = isset($setting['SITE_RTL']) && $setting['SITE_RTL'] == 'on';
    $isDark = isset($setting['cust_darklayout']) && $setting['cust_darklayout'] == 'on';
    if ($isRTL && $isDark) {
        $adminCssVariant = 'admin-rtl-dark';
    } elseif ($isDark) {
        $adminCssVariant = 'admin-ltr-dark';
    } elseif ($isRTL) {
        $adminCssVariant = 'admin-rtl-light';
    } else {
        $adminCssVariant = 'admin-ltr-light';
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', ($setting['currantLang'] ?? app()->getLocale())) }}" dir="{{ isset($setting['SITE_RTL']) && $setting['SITE_RTL'] == 'on'? 'rtl' : '' }}" id="html-dir-tag">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, minimal-ui" />
    @if (env('MOBILE_APP_FEEL', true))
        <meta name="theme-color" content="{{ isset($setting['color']) && $setting['color_flag'] == 'true' ? $setting['color'] : '#f8796c' }}">
        <meta name="theme-color" media="(prefers-color-scheme: dark)" content="#121212">
        @vite(['resources/css/mobile-app-feel.css'])
    @endif
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="author" content="WorkDo.io" />
    <meta name="base-url" content="{{ URL::to('/') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{--
        Turbo Drive (CLAUDE-6). Must stay in <head>: Turbo warns when started from
        <body>, and the onTurboLoad/onTurboOnce stub inside has to be defined
        before the first inline script in <body> calls it — 57 admin views do.

        Kill-switches: TURBO_ADMIN_ENABLED=false (admin only) or
        TURBO_ENABLED=false (everything), then `php artisan view:clear`.
    --}}
    @include('layouts.includes.turbo', ['entry' => 'admin'])

    <meta name="title" content="{{ isset($SuperadminData['metatitle']) ? $SuperadminData['metatitle'] : 'EcommerceGo' }}">
    <meta name="keywords" content="{{ isset($SuperadminData['metakeyword']) ? $SuperadminData['metakeyword'] : 'EcommerceGo, Store with Multi theme and Multi Store' }}">
    <meta name="description" content="{{ isset($SuperadminData['metadesc']) ? $SuperadminData['metadesc'] : 'Discover the efficiency of EcommerceGo, a user-friendly web application by Workdo.io.'}}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title" content="{{ isset($SuperadminData['metatitle']) ? $SuperadminData['metatitle'] : 'EcommerceGo' }}">
    <meta property="og:description" content="{{ isset($SuperadminData['metadesc']) ? $SuperadminData['metadesc'] : 'Discover the efficiency of EcommerceGo, a user-friendly web application by Workdo.io.'}} ">
    <meta property="og:image" content="{{ get_file_v(isset($SuperadminData['metaimage']) ? $SuperadminData['metaimage'] : 'storage/uploads/ecommercego-saas-preview.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:title" content="{{ isset($SuperadminData['metatitle']) ? $SuperadminData['metatitle'] : 'EcommerceGo' }}">
    <meta property="twitter:description" content="{{ isset($SuperadminData['metadesc']) ? $SuperadminData['metadesc'] : 'Discover the efficiency of EcommerceGo, a user-friendly web application by Workdo.io.'}} ">
    <meta property="twitter:image" content="{{ get_file_v(isset($SuperadminData['metaimage']) ? $SuperadminData['metaimage'] : 'storage/uploads/ecommercego-saas-preview.png') }}">

    <title>{{ isset($setting['title_text']) ? $setting['title_text'] : ( env('APP_NAME') ?? 'Ecommercego saas') }} - @yield('page-title') </title>

    @if (env('LEGACY_ADMIN_CSS', false))
        {{-- Kill-switch: Use legacy 28 separate CSS links instead of bundled Vite entry. --}}
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/select2.min.css') }}">
        <!-- Favicon icon -->
        <link rel="icon" href="{{ isset($setting['favicon']) ? get_file_v($setting['favicon']) : get_file_v(asset(Storage::url('uploads/logo/favicon.png'))) }}" type="image/x-icon" />

        <!-- notification css -->
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/notifier.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">
        <!-- datatable css -->
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">
        <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">
        <!-- Fonts -->
        <!-- font css -->
        <link rel="stylesheet" href="{{ asset('/assets/fonts/tabler-icons.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/assets/fonts/feather.css') }}">
        <link rel="stylesheet" href="{{ asset('/assets/fonts/fontawesome.css') }}">
        <link rel="stylesheet" href="{{ asset('/assets/fonts/material.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/css/plugins/dropzone.css') }}" type="text/css" />

        @if (isset($setting['cust_darklayout']) && isset($setting['SITE_RTL']) && $setting['cust_darklayout'] == 'on' && $setting['SITE_RTL'] == 'on')
            <link rel="stylesheet" href="{{ asset('public/assets/css/rtl-style-dark.css') }}" id="main-style-link">
            <link rel="stylesheet" href="{{ asset_v('css/rtl-custom.css') }}"  id="main-style-custom-link">
            <link rel="stylesheet" href="{{ asset_v('css/rtl-loader.css') }}" >
        @elseif(isset($setting['cust_darklayout']) && $setting['cust_darklayout'] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}" id="main-style-link">
            <link rel="stylesheet" href="{{ asset_v('css/custom.css') }}"  id="main-style-custom-link">
            <link rel="stylesheet" href="{{ asset_v('css/loader.css') }}" >
        @elseif(isset($setting['SITE_RTL']) && $setting['SITE_RTL'] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" id="main-style-link">
            <link rel="stylesheet" href="{{ asset_v('css/rtl-custom.css') }}"  id="main-style-custom-link">
            <link rel="stylesheet" href="{{ asset_v('css/rtl-loader.css') }}" >
        @else
            <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
            <link rel="stylesheet" href="{{ asset_v('css/custom.css') }}"  id="main-style-custom-link">
            <link rel="stylesheet" href="{{ asset_v('css/loader.css') }}" >
        @endif

        <link rel="stylesheet" href="{{ asset('assets/css/plugins/flatpickr.min.css') }}">

        <link rel="stylesheet" href="{{ asset('assets/css/summernote/summernote-bs4.css') }}">



        <link rel="stylesheet" href="{{ asset('css/emojionearea.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/calendar.css') }}">
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    @else
        {{-- New (P3): Bundle all admin CSS into 4 variants (RTL/LTR × dark/light) --}}
        <!-- Favicon icon -->
        <link rel="icon" href="{{ isset($setting['favicon']) ? get_file_v($setting['favicon']) : get_file_v(asset(Storage::url('uploads/logo/favicon.png'))) }}" type="image/x-icon" />

        @vite(['resources/css/' . $adminCssVariant . '.css'])

        {{-- Placeholder links for dark/RTL toggle in footerlink.blade.php. These have no href
             attribute (no network request) but carry the IDs that the toggle JavaScript expects
             to find. When the user toggles dark mode on the Settings page, the JS sets the href
             to the legacy CSS files, creating a live preview before the form submits and reloads
             with the correct variant. The placeholder links position after @vite so the toggled
             stylesheet cascades on top of the bundle. --}}
        <link rel="stylesheet" id="main-style-link">
        <link rel="stylesheet" id="main-style-custom-link">
    @endif


    <!-- Scripts -->

    <style>
        {!! isset($setting['storecss']) ? $setting['storecss'] :  '' !!}

        :root {
            --color-customColor: <?= $color ?? 'linear-gradient(141.55deg, rgba(240, 244, 243, 0) 3.46%, #ffffff 99.86%)' ?>;
            --bs-custom-color-border: <?= $color ?? '#ffff' ?>;
        }

        #storeList {
            display: none;
        }

        .dropdown-menu.show #storeList {
            display: block;
        }
        .select2-container {
            width: 100% !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {

        }
        .note-link-unlink-sample #commanModel{
            display: none !important;
        }
        .note-modal-footer {
            height: 50px;
        }

    </style>
     @if (app()->getLocale() == 'ar' || app()->getLocale() == 'he')
        <style>
            .select2-selection__rendered {
                float : right;
            }

        </style>
    @else
        <style>
        .select2-selection__rendered {
            float : left;
        }
        </style>
    @endif
    @stack('css')

    {!! isset($setting['storejs']) ? $setting['storejs'] :  '' !!}

</head>

<body class="{{ $themeColor ?? 'theme-3'}}">
    @include('partision.sidebar')

    @include('partision.header')

    <!-- [ Main Content ] start -->
    <div class="dash-container">
        <div class="dash-content">
           <!-- [ breadcrumb ] start -->
           <div class="page-header mb-md-4 mb-3">
                <div class="page-block d-flex align-items-center justify-content-between gap-3 flex-wrap">
                        <div class="page-header-wrp">
                            <div class="page-header-title">
                                <h2 class="h3 mb-0">@yield('page-title')</h4>
                            </div>
                            @if (\Request::route()->getName() != 'dashboard')
                            <ul class="breadcrumb mt-2">
                                <li class="breadcrumb-item">
                                        <a href="{{ route('dashboard') }}">{{ __('Home') }}</a>
                                    </li>
                                    @yield('breadcrumb')
                            </ul>
                            @endif
                        </div>
                        <div class="page-header-icon d-flex flex-wrap gap-2">
                            @yield('action-button')
                        </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->
            @yield('content')
        </div>
    </div>
    <!-- [ Main Content ] end -->

    @if (\Request::route()->getName() != 'pos.index')
        <div id="commanModel" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modelCommanModelLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content ">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modelCommanModelLabel"></h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body"></div>
                </div>
            </div>
        </div>

        <div id="commanModelOver" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modelCommanModelLabel"
        aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content ">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modelCommanModelLabel"></h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body"></div>
                </div>
            </div>
        </div>
    @else
        <div class="modal fade" id="commonModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    </div>
                </div>
            </div>
        </div>
    @endif


    <div id="loader" class="loader-wrapper" style="display: none;">
        <span class="site-loader"> </span>
        <h3 class="loader-content"> {{ __('Loading . . .') }} </h3>
    </div>

    @include('partision.settingPopup')
    @include('partision.footerlink')
    @stack('scripts')
    @stack('custom-script')
    @stack('custom-script1')
    <script type="text/javascript">
        function setActiveTheme(themeColor) {
            // Update the --active-theme-border variable based on the theme number
            document.documentElement.style.setProperty('--active-theme-border', `var(--bs-${themeColor}-border)`);
        }
        setActiveTheme("{{$themeColor}}");
        {{--
            $(document).ready → onTurboLoad (CLAUDE-6). ready() fires once per
            document, so from the second Turbo page onward select2 was never
            initialised anywhere in the admin — every .select2 <select> rendered as
            a bare dropdown.

            The `.length > 0` guard was not enough on its own: select2 leaves its
            class on the element, so a second run finds it again and builds a
            duplicate container. `:not(.select2-hidden-accessible)` is the marker
            select2 itself sets, so already-initialised elements are skipped.
            turbo-cleanup.js destroys them on turbo:before-cache, so the cached
            snapshot holds plain <select> markup and this initialiser gets a clean
            DOM on the way back.
        --}}
        window.onTurboLoad(function () {
            var $select2 = $('.select2:not(.select2-hidden-accessible)');
            if ($select2.length > 0) {
                $select2.select2({
                    tags: true,
                    createTag: function (params) {
                      var term = $.trim(params.term);
                      if (term === '') {
                        return null;
                      }
                      return {
                        id: term,
                        text: term,
                        newTag: true
                      };
                    }
                });
            }
        })
        </script>
    <script>
        function add_more_choice_option(i, name) {
            // Create a safe CSS selector by removing special characters
            var safe_i = i.toString().replace(/[^a-zA-Z0-9_-]/g, '_');

        $('#attribute_options').append(
            '<div class="card oprtion"><div class="card-body "><input type="hidden" class="abd" name="attribute_no[]" value="' +
            i + '"><input type="hidden" class="abd" name="option_no[]" value="' + i + '">' +

            '<div class="row">' +
            '<div class="form-group col-lg-6 col-12">' +
            '<label for="choice_attributes" class="col-6">' + name + ':</label></div>' +

            '<div class="form-group col-lg-6 col-12 d-flex justify-content-end all-button-box">' +
            '<a href="#" class="btn btn-sm btn-primary add_attribute btn-badge" data-ajax-popup="true" data-title="{{ __('Add Attribute Option') }}" data-size="md" ' +
            'data-url="{{ route('product-attribute-option.create') }}/' + i + '" ' +
            'data-toggle="tooltip">' +
            '<i class="ti ti-plus">{{ __('Add Attribute Option') }}</i></a></div></div>' +

            '<div class="row parent-clase">' +
            '<div class="form-group col-12">' +
            '<div class="form-chec1k form-switch p-0">' +
            '<input type="hidden" name="visible_attribute_' + i + '" value="0">' +
            '<input type="checkbox" class="form-check-input attribute-form-check" name="visible_attribute_' + i +
            '" id="visible_attribute_' + safe_i + '" value="1">' +
            '<label class="form-check-label" for="visible_attribute_' + safe_i + '"></label>' +
            '<label for="visible_attribute_' + safe_i + '" class=""> Visible on the product page</label></div>' +

            ' <div style="margin-top: 9px;"></div>' +

            '<div class="for-variation_data form-chec1k form-switch p-0 d-none use_for_variation" id="use_for_variation_' + safe_i + '"  data-id="' +
            i + '">' +
            '<input type="hidden" name="for_variation_' + i + '" value="0">' +
            '<input type="checkbox" class="form-check-input input-options attribute-form-check enable_variation_' +
            safe_i + '" name="for_variation_' + i + '" id="for_variation_' + safe_i + '" value="1" data-id="' + i +
            '" data-enable-variation=" enable_variation_' + safe_i + ' ">' +
            '<label class="form-check-label" for="for_variation_' + safe_i + '"></label>' +
            '<label for="for_variation_' + safe_i + '" class=""> Used for variations</label></div>' +
            '</div>' +

            '<div class="form-group col-12">' +
            '<select class="col-6 form-control attribute attribute_option_data" name="attribute_options_' + i +
            '[]" __="{{ __('Enter choice values') }}"  data-role="" multiple id="attribute' + safe_i +
            '" data-id="' + i + '" required ></select></div></div>' +

            '</div></div>');

        if ($('.enable_product_variant').prop('checked') == true) {
            $(".use_for_variation").removeClass("d-none");
        }

        comman_function();
    }
    {{--
        Delegation moved into onTurboOnce (CLAUDE-6). These two are bound to
        `document`, which Turbo never replaces, so re-executing this block on every
        navigation stacked another pair each time. turbo-dedupe.blade.php would have
        caught it, but an explicit registration is clearer and does not depend on the
        guard being installed.
    --}}
    window.onTurboOnce(function () {
        $(document).on('click', '.note-btn', function () {
            var ariaLabel = $(this).attr('aria-label');
            if (ariaLabel === 'Link (CTRL+K)' || ariaLabel === 'Edit') {
                $('body').addClass('note-link-unlink-sample');
            }
        });
        $(document).on('click', '.close, .note-link-btn', function () {
            $('body').removeClass('note-link-unlink-sample');
        });
    }, 'admin-summernote-link-modal');

    {{--
        Sidebar search: bound directly to #sideBarsearch, which lives in
        partision/sidebar.blade.php — inside the body Turbo swaps. So this needs
        onTurboLoad (re-bind per page), not onTurboOnce, and .off() first in case the
        same element survives a preview render.
    --}}
    window.onTurboLoad(function () {
    $('#sideBarsearch').off('keyup.sidebarSearch').on('keyup.sidebarSearch', function () {
        let value = $(this).val().toLowerCase().trim();

        if (value === "") {
            // If input is empty, show all
            $('.dash-navbar > .dash-item').show();
            $('.dash-submenu').hide();
            return;
        }

        $('.dash-navbar > .dash-item').each(function () {
            let $item = $(this);
            let isMatch = false;

            // Main menu match
            let mainText = $item.find('> .dash-link .dash-mtext').text().toLowerCase();
            if (mainText.includes(value)) {
                isMatch = true;
            }

            // Submenu match
            $item.find('.dash-submenu .dash-item').each(function () {
                let $subItem = $(this);
                let subText = $subItem.find('.dash-link').text().toLowerCase();

                if (subText.includes(value)) {
                    isMatch = true;
                    $subItem.show();
                } else {
                    $subItem.hide();
                }
            });

            if (isMatch) {
                $item.show();
                $item.find('.dash-submenu').show();
            } else {
                $item.hide();
            }
        });
    });
    }); {{-- end onTurboLoad (sidebar search) --}}
</script>
    @if (Session::has('success'))
        <script>
            show_toastr('{{ __('Success') }}', '{!! Session::get('success') !!}', 'success');
        </script>
        <?php Session::forget('success'); ?>
    @endif

    @if (Session::has('error'))
        <script>
            show_toastr('{{ __('Error') }}', '{!! Session::get('error') !!}', 'error');
        </script>
        <?php Session::forget('error'); ?>
    @endif


    @php
        $setting = getSuperAdminAllSetting();
    @endphp
    @if (isset($setting['enable_cookie']) && $setting['enable_cookie'] == 'on')
        @include('layouts.cookie_consent')
    @endif
</body>

</html>
