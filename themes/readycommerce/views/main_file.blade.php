@extends('front_end.layouts.app')

@section('page-title')
    @if (request()->has('rc_product'))
        {{ __('Product Details') }}
    @elseif (request()->has('rc_checkout'))
        {{ __('Checkout') }}
    @else
        {{ __('Home Page') }}
    @endif
@endsection

@section('content')
@php extract(include base_path('themes/readycommerce/views/components/readycart-data.php')); @endphp
@php
    $storeSlug = $slug ?? ($store->slug ?? request()->route('storeSlug') ?? 'stylique');
    $storeObj = isset($store) && is_object($store) ? $store : \App\Models\Store::where('slug', $storeSlug)->first();
    $storeId = $storeObj ? (int) $storeObj->id : 2;
    $themeSettings = $themeSettings ?? (function_exists('getThemeSetting') ? getThemeSetting($storeId, $storeObj->theme_id ?? 'readycommerce') : []);
    $productsUrl = route('page.product-list', ['storeSlug' => $storeSlug]);

    $slides = [
        [
            'image' => asset('readycart/hero-watch.png'),
            'eyebrow' => 'Black Friday',
            'title' => 'Modern Watch',
            'sub' => 'Super Sale',
            'badge' => 'Save up to 70%',
            'link' => $productsUrl,
            'tone' => 'text-white',
        ],
        [
            'image' => asset('readycart/banner-beauty.png'),
            'eyebrow' => 'New Arrivals',
            'title' => 'Natural Beauty',
            'sub' => 'Skincare Week',
            'badge' => 'Up to 50% off',
            'link' => $productsUrl,
            'tone' => 'text-ink',
        ],
        [
            'image' => asset('readycart/banner-outdoor.png'),
            'eyebrow' => 'Limited Time',
            'title' => 'Outdoor Flash',
            'sub' => 'Biggest Deals',
            'badge' => 'Up to 60% off',
            'link' => $productsUrl,
            'tone' => 'text-ink',
        ],
    ];

    if (!empty($themeSettings['slider_repeater'])) {
        $customSliders = json_decode($themeSettings['slider_repeater'], true);
        if (is_array($customSliders) && !empty($customSliders)) {
            $parsedSlides = [];
            foreach ($customSliders as $cs) {
                if (!empty($cs['image'])) {
                    $img = (str_starts_with($cs['image'], 'readycart/') || str_starts_with($cs['image'], 'http')) ? asset($cs['image']) : get_file($cs['image']);
                    $parsedSlides[] = [
                        'image' => $img,
                        'eyebrow' => $cs['small_text'] ?? 'Special Offer',
                        'title' => $cs['big_text'] ?? 'Modern Collection',
                        'sub' => $cs['content'] ?? 'Limited Time Offer',
                        'badge' => $cs['button_text'] ?? 'Shop Now',
                        'link' => !empty($cs['button_link']) ? $cs['button_link'] : $productsUrl,
                        'tone' => 'text-white',
                    ];
                }
            }
            if (!empty($parsedSlides)) {
                $slides = $parsedSlides;
            }
        }
    }

    $showSlider = !isset($themeSettings['slider_status']) || $themeSettings['slider_status'] == 1;
    $showSideBanners = !isset($themeSettings['more_offer_status']) || $themeSettings['more_offer_status'] == 1;
    $sideBanner1Img = !empty($themeSettings['more_offer_image']) ? get_file($themeSettings['more_offer_image']) : asset('readycart/banner-beauty.png');
    $sideBanner1Link = !empty($themeSettings['more_offer_button_link']) ? $themeSettings['more_offer_button_link'] : ($productsUrl . '?category=' . urlencode('Beauty & Personal Care'));
    $sideBanner2Img = !empty($themeSettings['more_offer_image2']) ? get_file($themeSettings['more_offer_image2']) : asset('readycart/banner-outdoor.png');
    $sideBanner2Link = !empty($themeSettings['more_offer_button_link2']) ? $themeSettings['more_offer_button_link2'] : ($productsUrl . '?category=' . urlencode('Sports & Outdoors'));

    $showCategories = !isset($themeSettings['category_status']) || $themeSettings['category_status'] == 1;
    $categoriesTitle = !empty($themeSettings['category_title']) ? $themeSettings['category_title'] : 'Shop By Categories';

    $showPopular = !isset($themeSettings['popular_products_status']) || $themeSettings['popular_products_status'] == 1;
    $popularTitle = !empty($themeSettings['popular_products_title']) ? $themeSettings['popular_products_title'] : 'Popular Products';

    $showBestDeal = !isset($themeSettings['bestseller_status']) || $themeSettings['bestseller_status'] == 1;
    $bestDealTitle = !empty($themeSettings['bestseller_title']) ? $themeSettings['bestseller_title'] : 'Best Deal';

    $showBrands = !isset($themeSettings['logo_status']) || $themeSettings['logo_status'] == 1;
    $brandsTitle = !empty($themeSettings['logo_title']) ? $themeSettings['logo_title'] : 'Top Brands';

    $showForYou = !isset($themeSettings['product_status']) || $themeSettings['product_status'] == 1;
    $forYouTitle = !empty($themeSettings['product_title']) ? $themeSettings['product_title'] : 'Just For You';

    $popular = array_slice($rcRotate($rcProducts, 0), 0, 6);
    $bestDealSorted = $rcProducts;
    usort($bestDealSorted, fn ($a, $b) => (($b['oldPrice'] - $b['price']) <=> ($a['oldPrice'] - $a['price'])));
    $bestDeal = array_slice($bestDealSorted, 0, 6);
    $forYouAll = array_slice(array_merge($rcRotate($rcProducts, 3), $rcRotate($rcProducts, 6)), 0, 18);

    $mobileOverflow = count($rcCategories) > 4;
    $desktopOverflow = count($rcCategories) > 8;
    $shouldDuplicate = $mobileOverflow || $desktopOverflow;
    $visibleCategories = $shouldDuplicate ? array_merge($rcCategories, $rcCategories) : $rcCategories;
@endphp

@if (request()->has('rc_product'))
    @include('front_end.pages.rc-product-detail-inline', ['rcProductId' => (int) request('rc_product')])
@elseif (request()->has('rc_checkout'))
    @include('front_end.pages.rc-checkout-inline')
@else
    <main class="mx-auto max-w-[1400px] px-3 pb-8 sm:px-4 lg:px-6">
        {{-- Top Featured 3-Column Section --}}
        <section class="grid gap-4 lg:grid-cols-[250px_minmax(0,1fr)]" aria-label="Featured">
            {{-- Left Categories Sidebar --}}
            <aside class="hidden w-[250px] flex-col overflow-hidden rounded-lg border border-line bg-white shadow-sm lg:flex" aria-label="Categories">
                <ul class="p-1.5">
                    @foreach ($rcCategories as $i => $catName)
                        <li>
                            <a
                                href="{{ $productsUrl }}?category={{ urlencode($catName) }}"
                                class="group flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left text-sm text-ink/80 transition hover:bg-brand-soft hover:text-brand"
                            >
                                @switch($i)
                                    @case(0)
                                        {{-- Laptop --}}
                                        <svg class="size-4 text-ink/50 group-hover:text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 16V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v9m16 0H4m16 0 1.28 2.55a1 1 0 0 1-.9 1.45H3.62a1 1 0 0 1-.9-1.45L4 16"/></svg>
                                        @break
                                    @case(1)
                                        {{-- Shirt --}}
                                        <svg class="size-4 text-ink/50 group-hover:text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"/></svg>
                                        @break
                                    @case(2)
                                        {{-- Sparkles --}}
                                        <svg class="size-4 text-ink/50 group-hover:text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/></svg>
                                        @break
                                    @case(3)
                                        {{-- Sofa --}}
                                        <svg class="size-4 text-ink/50 group-hover:text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v3"/><path d="M2 11v5a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5a2 2 0 0 0-4 0v2H6v-2a2 2 0 0 0-4 0Z"/><path d="M4 18v2"/><path d="M20 18v2"/><path d="M12 4v9"/></svg>
                                        @break
                                    @case(4)
                                        {{-- ShoppingBasket --}}
                                        <svg class="size-4 text-ink/50 group-hover:text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 11-1 9"/><path d="m19 11-4-7"/><path d="M2 11h20"/><path d="m3.5 11 1.6 7.4a2 2 0 0 0 2 1.6h9.8a2 2 0 0 0 2-1.6l1.7-7.4"/><path d="M4.5 15.5h15"/><path d="m5 11 4-7"/><path d="m9 11 1 9"/></svg>
                                        @break
                                    @case(5)
                                        {{-- Gamepad2 --}}
                                        <svg class="size-4 text-ink/50 group-hover:text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="6" x2="10" y1="11" y2="11"/><line x1="8" x2="8" y1="9" y2="13"/><line x1="15" x2="15.01" y1="12" y2="12"/><line x1="18" x2="18.01" y1="10" y2="10"/><path d="M17.32 5H6.68a4 4 0 0 0-3.978 3.59c-.006.052-.01.101-.017.152C2.604 9.416 2 14.456 2 16a3 3 0 0 0 3 3c1 0 1.5-.5 2-1l1.414-1.414A2 2 0 0 1 9.828 16h4.344a2 2 0 0 1 1.414.586L17 18c.5.5 1 1 2 1a3 3 0 0 0 3-3c0-1.545-.604-6.584-.685-7.258-.007-.05-.011-.1-.017-.151A4 4 0 0 0 17.32 5z"/></svg>
                                        @break
                                    @case(6)
                                        {{-- Dumbbell --}}
                                        <svg class="size-4 text-ink/50 group-hover:text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.4 14.4 9.6 9.6"/><path d="M18.657 21.485a2 2 0 1 1-2.829-2.828l-1.767 1.768a2 2 0 1 1-2.829-2.829l6.364-6.364a2 2 0 1 1 2.829 2.829l-1.768 1.767a2 2 0 1 1 2.828 2.829z"/><path d="m21.5 21.5-1.4-1.4"/><path d="M3.9 3.9 2.5 2.5"/><path d="M6.404 12.768a2 2 0 1 1-2.829-2.829l1.768-1.767a2 2 0 1 1-2.828-2.829l2.828-2.828a2 2 0 1 1 2.829 2.828l1.767-1.768a2 2 0 1 1 2.829 2.829z"/></svg>
                                        @break
                                    @default
                                        {{-- Gem --}}
                                        <svg class="size-4 text-ink/50 group-hover:text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h12l4 6-10 13L2 9Z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/></svg>
                                @endswitch
                                <span class="flex-1">{{ $catName }}</span>
                                <svg class="size-4 text-ink/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <a
                    href="{{ $productsUrl }}"
                    class="mt-auto flex items-center gap-2 border-t border-line px-4 py-3 text-left text-sm font-semibold text-ink/80 transition hover:text-brand"
                >
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 12h18"/><path d="M12 3v18"/></svg>
                    View All Categories
                </a>
            </aside>

            {{-- Right Hero + Side Banners + Store Promises --}}
            <div class="flex min-w-0 flex-col gap-4">
                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_260px]">
                    {{-- Hero Slider --}}
                    <div data-rc-hero class="group/hero relative h-[260px] touch-pan-y overflow-hidden rounded-lg bg-ink shadow-md sm:h-[320px] lg:h-full lg:min-h-[330px]">
                        @foreach ($slides as $i => $slide)
                            <div
                                data-rc-slide="{{ $i }}"
                                aria-hidden="{{ $i === 0 ? 'false' : 'true' }}"
                                class="absolute inset-0 transition-opacity duration-700 {{ $i === 0 ? 'opacity-100 z-[1]' : 'opacity-0 pointer-events-none z-0' }}"
                            >
                                <img src="{{ $slide['image'] }}" alt="" class="absolute inset-0 h-full w-full object-cover" @if($i === 0) fetchpriority="high" @else loading="lazy" @endif />
                                <div class="relative flex h-full flex-col justify-center gap-2 p-6 sm:p-10 {{ $slide['tone'] }}">
                                    <span class="w-fit rounded-sm bg-brand px-2.5 py-1 text-[11px] font-bold uppercase tracking-widest text-brand-foreground">{{ $slide['eyebrow'] }}</span>
                                    <h2 class="max-w-[14ch] text-3xl font-extrabold leading-[1.05] tracking-tight text-balance sm:text-5xl">{{ $slide['title'] }}</h2>
                                    <p class="text-lg font-bold opacity-90 sm:text-xl">{{ $slide['sub'] }}</p>
                                    <p class="text-sm font-medium opacity-80">{{ $slide['badge'] }}</p>
                                    <a
                                        href="{{ $slide['link'] ?? $productsUrl }}"
                                        class="mt-1 flex w-fit items-center gap-2 rounded-md bg-white px-5 py-2.5 text-sm font-semibold text-ink transition hover:bg-brand hover:text-brand-foreground"
                                    >
                                        {{ $slide['badge'] ?? 'Shop Now' }}
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach

                        <div class="pointer-events-none absolute inset-x-3 top-1/2 z-10 flex -translate-y-1/2 justify-between">
                            <button
                                type="button"
                                data-rc-hero-prev
                                aria-label="Previous slide"
                                class="pointer-events-auto flex size-9 items-center justify-center rounded-full bg-white/90 text-ink shadow-md opacity-0 transition hover:bg-white group-hover/hero:opacity-100 focus-visible:opacity-100"
                            >
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                            </button>
                            <button
                                type="button"
                                data-rc-hero-next
                                aria-label="Next slide"
                                class="pointer-events-auto flex size-9 items-center justify-center rounded-full bg-white/90 text-ink shadow-md opacity-0 transition hover:bg-white group-hover/hero:opacity-100 focus-visible:opacity-100"
                            >
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </button>
                        </div>

                        <div class="absolute bottom-3 left-1/2 z-10 flex -translate-x-1/2 gap-1.5" role="tablist" aria-label="Hero slides">
                            @foreach ($slides as $i => $slide)
                                <button
                                    type="button"
                                    role="tab"
                                    data-rc-hero-dot="{{ $i }}"
                                    aria-selected="{{ $i === 0 ? 'true' : 'false' }}"
                                    aria-label="Slide {{ $i + 1 }}"
                                    class="h-1.5 rounded-full transition-all {{ $i === 0 ? 'w-6 bg-brand' : 'w-1.5 bg-white/70' }}"
                                ></button>
                            @endforeach
                        </div>
                    </div>

                    {{-- 2 Stacked Right Side Banners --}}
                    @if ($showSideBanners)
                    <div class="grid grid-cols-2 gap-4 lg:flex lg:flex-col">
                        <a href="{{ $sideBanner1Link }}" class="group relative min-h-[140px] flex-1 overflow-hidden rounded-lg text-left shadow-sm">
                            <img src="{{ $sideBanner1Img }}" alt="" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                            <span class="relative flex h-full flex-col justify-end gap-0.5 bg-gradient-to-t from-ink/60 via-transparent to-transparent p-4 text-white">
                                <span class="text-[11px] font-semibold uppercase tracking-widest opacity-90">Special Promo</span>
                                <span class="text-base font-extrabold leading-tight">Featured Offer</span>
                            </span>
                        </a>
                        <a href="{{ $sideBanner2Link }}" class="group relative min-h-[140px] flex-1 overflow-hidden rounded-lg text-left shadow-sm">
                            <img src="{{ $sideBanner2Img }}" alt="" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                            <span class="relative flex h-full flex-col justify-end gap-0.5 bg-gradient-to-t from-ink/60 via-transparent to-transparent p-4 text-white">
                                <span class="text-[11px] font-semibold uppercase tracking-widest opacity-90">Limited Offer</span>
                                <span class="text-base font-extrabold leading-tight">Flash Sale</span>
                            </span>
                        </a>
                    </div>
                    @endif
                </div>

                {{-- 5 Store Promises --}}
                <section aria-label="Store promises" class="rounded-lg border border-line bg-white p-1.5 shadow-sm">
                    <ul class="grid grid-cols-2 gap-1 md:grid-cols-3 xl:grid-cols-5 xl:gap-0">
                        <li class="flex items-center gap-2.5 rounded-md p-2.5 xl:border-r xl:border-line">
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-full border border-violet-100 bg-violet-50 text-violet-700">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2"/><path d="M3 11h3c.8 0 1.6.3 2.1.9l1.1.9c1.6 1.6 4.1 1.6 5.7 0l1.1-.9c.5-.6 1.3-.9 2.1-.9H21"/></svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-[13px] font-semibold text-ink">Secure Payments</span>
                                <span class="block truncate text-[11px] text-ink/55">100% safe &amp; trusted</span>
                            </span>
                        </li>
                        <li class="flex items-center gap-2.5 rounded-md p-2.5 xl:border-r xl:border-line">
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-full border border-emerald-100 bg-emerald-50 text-emerald-700">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-[13px] font-semibold text-ink">Free Delivery</span>
                                <span class="block truncate text-[11px] text-ink/55">On orders over $999</span>
                            </span>
                        </li>
                        <li class="flex items-center gap-2.5 rounded-md p-2.5 xl:border-r xl:border-line">
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-full border border-orange-100 bg-orange-50 text-orange-700">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M13 16H8"/></svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-[13px] font-semibold text-ink">100% Authentic</span>
                                <span class="block truncate text-[11px] text-ink/55">Genuine products only</span>
                            </span>
                        </li>
                        <li class="flex items-center gap-2.5 rounded-md p-2.5 xl:border-r xl:border-line">
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-full border border-lime-100 bg-lime-50 text-lime-700">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5Zm0 0a9 9 0 1 1 18 0m0 0v5a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3Z"/><path d="M21 16v2a4 4 0 0 1-4 4h-5"/></svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-[13px] font-semibold text-ink">24/7 Support</span>
                                <span class="block truncate text-[11px] text-ink/55">We are always here</span>
                            </span>
                        </li>
                        <li class="flex items-center gap-2.5 rounded-md p-2.5">
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-full border border-blue-100 bg-blue-50 text-blue-700">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-[13px] font-semibold text-ink">Easy Returns</span>
                                <span class="block truncate text-[11px] text-ink/55">Hassle-free returns</span>
                            </span>
                        </li>
                    </ul>
                </section>
            </div>
        </section>

        {{-- Shop By Categories --}}
        @if ($showCategories)
        <section class="mt-10" aria-label="Shop by categories">
            <div class="mb-4 flex items-end justify-between">
                <h2 class="text-lg font-bold tracking-tight sm:text-xl">{{ $categoriesTitle }}</h2>
                <a href="{{ $productsUrl }}" class="flex items-center gap-1 text-sm text-ink/60 transition hover:text-brand">
                    View All
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
            <div class="rc-category-carousel" aria-label="Product categories">
                <ul
                    class="rc-category-track flex w-max"
                    data-mobile-overflow="{{ $mobileOverflow ? 'true' : 'false' }}"
                    data-desktop-overflow="{{ $desktopOverflow ? 'true' : 'false' }}"
                    style="--marquee-duration: {{ max(18, count($rcCategories) * 3) }}s"
                >
                    @foreach ($visibleCategories as $idx => $catName)
                        @php $duplicate = $idx >= count($rcCategories); @endphp
                        <li class="rc-category-item px-2 pb-2">
                            <a
                                href="{{ $productsUrl }}?category={{ urlencode($catName) }}"
                                @if ($duplicate) aria-hidden="true" tabindex="-1" @endif
                                class="rc-category-button group flex w-full flex-col items-center gap-2 text-center"
                            >
                                <span class="rc-category-icon flex size-16 items-center justify-center overflow-hidden rounded-full border border-line bg-surface shadow-sm transition-all duration-300 sm:size-20">
                                    <img
                                        src="{{ $rcCategoryImages[$catName] ?? asset('readycart/p-heater.png') }}"
                                        alt=""
                                        width="96"
                                        height="96"
                                        loading="lazy"
                                        class="rc-category-image size-full object-contain p-2.5 mix-blend-multiply transition-transform duration-300"
                                    />
                                </span>
                                <span class="text-[11px] font-semibold text-balance sm:text-xs">{{ $catName }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
        @endif

        {{-- Popular Products --}}
        @if ($showPopular)
        <section class="mt-10" aria-label="Popular products">
            <div class="mb-4 flex items-end justify-between">
                <h2 class="text-lg font-bold tracking-tight sm:text-xl">{{ $popularTitle }}</h2>
                <a href="{{ $productsUrl }}" class="flex items-center gap-1 text-sm text-ink/60 transition hover:text-brand">
                    View All
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
            <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                @foreach ($popular as $product)
                    <x-product-card :store="$store" :product="$product" />
                @endforeach
            </div>
        </section>
        @endif

        {{-- Best Deal --}}
        @if ($showBestDeal)
        <section class="mt-10" aria-label="Best deals">
            <div class="mb-4 flex items-end justify-between">
                <h2 class="text-lg font-bold tracking-tight sm:text-xl">{{ $bestDealTitle }}</h2>
                <a href="{{ $productsUrl }}" class="flex items-center gap-1 text-sm text-ink/60 transition hover:text-brand">
                    View All
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
            <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                @foreach ($bestDeal as $product)
                    <x-product-card :store="$store" :product="$product" />
                @endforeach
            </div>
        </section>
        @endif

        {{-- Dynamic Custom Category Shelf 1 --}}
        @if (!empty($themeSettings['custom_cat1_status']) && $themeSettings['custom_cat1_status'] == 1 && !empty($themeSettings['custom_cat1_name']))
            @php
                $cat1Target = strtolower(trim($themeSettings['custom_cat1_name']));
                $cat1Products = array_values(array_filter($rcProducts, fn($p) => strtolower(trim($p['category'] ?? '')) === $cat1Target));
                $cat1Title = !empty($themeSettings['custom_cat1_title']) ? $themeSettings['custom_cat1_title'] : $themeSettings['custom_cat1_name'];
            @endphp
            @if (!empty($cat1Products))
            <section class="mt-10" aria-label="{{ $cat1Title }}">
                <div class="mb-4 flex items-end justify-between">
                    <h2 class="text-lg font-bold tracking-tight sm:text-xl">{{ $cat1Title }}</h2>
                    <a href="{{ $productsUrl }}?category={{ urlencode($themeSettings['custom_cat1_name']) }}" class="flex items-center gap-1 text-sm text-ink/60 transition hover:text-brand">
                        View All
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                    @foreach (array_slice($cat1Products, 0, 6) as $product)
                        <x-product-card :store="$store" :product="$product" />
                    @endforeach
                </div>
            </section>
            @endif
        @endif

        {{-- Dynamic Custom Category Shelf 2 --}}
        @if (!empty($themeSettings['custom_cat2_status']) && $themeSettings['custom_cat2_status'] == 1 && !empty($themeSettings['custom_cat2_name']))
            @php
                $cat2Target = strtolower(trim($themeSettings['custom_cat2_name']));
                $cat2Products = array_values(array_filter($rcProducts, fn($p) => strtolower(trim($p['category'] ?? '')) === $cat2Target));
                $cat2Title = !empty($themeSettings['custom_cat2_title']) ? $themeSettings['custom_cat2_title'] : $themeSettings['custom_cat2_name'];
            @endphp
            @if (!empty($cat2Products))
            <section class="mt-10" aria-label="{{ $cat2Title }}">
                <div class="mb-4 flex items-end justify-between">
                    <h2 class="text-lg font-bold tracking-tight sm:text-xl">{{ $cat2Title }}</h2>
                    <a href="{{ $productsUrl }}?category={{ urlencode($themeSettings['custom_cat2_name']) }}" class="flex items-center gap-1 text-sm text-ink/60 transition hover:text-brand">
                        View All
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                    @foreach (array_slice($cat2Products, 0, 6) as $product)
                        <x-product-card :store="$store" :product="$product" />
                    @endforeach
                </div>
            </section>
            @endif
        @endif

        {{-- Top Brands --}}
        @if ($showBrands)
        <section id="brands" class="mt-10" aria-label="Top brands">
            <div class="mb-4 flex items-end justify-between">
                <h2 class="text-lg font-bold tracking-tight sm:text-xl">{{ $brandsTitle }}</h2>
                <a href="{{ $productsUrl }}" class="flex items-center gap-1 text-sm text-ink/60 transition hover:text-brand">
                    View All
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
            <ul class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @foreach (array_slice($rcBrands, 0, 6) as $i => $brandName)
                    <li class="flex flex-col items-center gap-2 rounded-lg border border-line bg-white p-3 text-center transition hover:border-brand/40">
                        <span class="flex size-12 items-center justify-center rounded-full bg-brand-soft text-lg font-extrabold text-brand">{{ substr($brandName, 0, 1) }}</span>
                        <div>
                            <p class="text-sm font-semibold">{{ $brandName }}</p>
                            <p class="text-[11px] text-ink/55">{{ 3 + ($i * 2) }} Products</p>
                        </div>
                        <a
                            href="{{ $productsUrl }}?brand={{ urlencode($brandName) }}"
                            class="flex h-8 w-full items-center justify-center rounded-md border border-brand text-xs font-semibold text-brand transition hover:bg-brand hover:text-brand-foreground"
                        >
                            Visit Brand
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
        @endif

        {{-- Just For You --}}
        @if ($showForYou)
        <section class="mt-10" aria-label="Just for you">
            <div class="mb-4 flex items-end justify-between">
                <h2 class="text-lg font-bold tracking-tight sm:text-xl">{{ $forYouTitle }}</h2>
                <a href="{{ $productsUrl }}" class="flex items-center gap-1 text-sm text-ink/60 transition hover:text-brand">
                    View All
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
            <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6" data-rc-for-you-grid>
                @foreach ($forYouAll as $idx => $product)
                    <div @if ($idx >= 12) data-rc-extra-product class="hidden" @endif>
                        <x-product-card :store="$store" :product="$product" />
                    </div>
                @endforeach
            </div>
            <div class="mt-6 flex justify-center" data-rc-load-more-wrap>
                <button
                    type="button"
                    data-rc-load-more
                    class="h-10 rounded-md border border-brand px-8 text-sm font-semibold text-brand transition hover:bg-brand hover:text-brand-foreground"
                >
                    Load More Products
                </button>
            </div>
        </section>
        @endif
    </main>
@endif
@endsection


