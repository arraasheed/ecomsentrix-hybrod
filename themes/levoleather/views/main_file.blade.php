@extends('front_end.layouts.app')

@section('page-title')
    {{ __('Home Page') }}
@endsection

@section('content')
    <main>
        {{-- Demo Showcase 3 — left: TOP CATEGORIES menu + right: hero carousel (levoleather) --}}
        @if (isset($themeSettings['slider_status']) && $themeSettings['slider_status'] == 1)
            <section class="levo-showcase section-spacing pb-0" style="background: var(--levo-cream, #faf5ef);">
                <div class="md:container mx-auto px-4 w-full">
                    <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
                        {{-- Left: TOP CATEGORIES (demo Showcase 3) --}}
                        <div class="hidden lg:block lg:w-[260px] shrink-0">
                            <div class="bg-white rounded-xl border overflow-hidden" style="border-color:#f0e4d8;">
                                <div class="flex items-center gap-2 px-4 py-3 text-white" style="background: var(--levo-leather,#1a1a1a);">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                                    <span class="font-bold text-sm tracking-wide">{{ __('TOP CATEGORIES') }}</span>
                                </div>
                                <ul class="divide-y" style="border-color:#f0e4d8;">
                                    @foreach (collect($MainCategoryList)->take(10) as $cat)
                                        <li>
                                            <a href="{{ route('collections.all', [$slug, $cat->slug]) }}" class="flex items-center justify-between px-4 py-2.5 text-sm hover:bg-[#faf5ef] transition">
                                                <span class="text-gray-800">{{ $cat->name }}</span>
                                                <svg width="14" height="14" viewBox="0 0 330 330" fill="#999"><path d="M250.6 154.4l-150-150c-5.9-5.9-15.4-5.9-21.2 0 -5.9 5.9-5.9 15.4 0 21.2l139.4 139.4 -139.4 139.4c-5.9 5.9-5.9 15.4 0 21.2 2.9 2.9 6.7 4.4 10.6 4.4s7.7-1.5 10.6-4.4l150-150c2.8-2.8 4.4-6.6 4.4-10.6S253.4 157 250.6 154.4z"/></svg>
                                            </a>
                                        </li>
                                    @endforeach
                                    <li>
                                        <a href="{{ route('collections', $slug) }}" class="flex items-center justify-between px-4 py-2.5 text-sm font-semibold hover:bg-[#faf5ef] transition" style="color: var(--levo-primary,#e8007c);">
                                            <span>{{ __('All Categories') }}</span>
                                            <svg width="14" height="14" viewBox="0 0 330 330" fill="currentColor"><path d="M250.6 154.4l-150-150c-5.9-5.9-15.4-5.9-21.2 0 -5.9 5.9-5.9 15.4 0 21.2l139.4 139.4 -139.4 139.4c-5.9 5.9-5.9 15.4 0 21.2 2.9 2.9 6.7 4.4 10.6 4.4s7.7-1.5 10.6-4.4l150-150c2.8-2.8 4.4-6.6 4.4-10.6S253.4 157 250.6 154.4z"/></svg>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        {{-- Right: Hero carousel (existing slider data, rounded 14px) --}}
                        <div class="flex-1 min-w-0">
                            <div class="swiper hero-slider rounded-xl overflow-hidden levo-hero border" style="border-color:#f0e4d8;">
                                <div class="swiper-wrapper">
                                    @php $sliders = json_decode($themeSettings['slider_repeater'] ?? '[]', true); if (!is_array($sliders)) $sliders = []; @endphp
                                    @foreach ($sliders as $slider_repeater)
                                        <div class="swiper-slide h-auto relative">
                                            <img src="{{ get_file($slider_repeater['image']) }}" width="900" height="400" alt="{{ $slider_repeater['big_text'] ?? 'New arrivals' }}" loading="lazy" class="w-full h-full object-cover object-center absolute inset-0 rounded-xl" />
                                            <div class="relative z-10 flex items-center md:py-[90px] py-[60px]">
                                                <div class="px-4 md:px-8 max-w-[520px]">
                                                    <h2 class="text-3xl md:text-4xl text-black font-extrabold mb-3 leading-tight" style="font-family:'Satoshi', sans-serif;">
                                                        {!! $slider_repeater['big_text'] ?? __('New Arrivals') !!}
                                                    </h2>
                                                    <p class="md:mb-6 mb-4 text-black/80 max-w-sm text-sm md:text-base">
                                                        {!! $slider_repeater['content'] ?? '' !!}
                                                    </p>
                                                    <a href="{{ url($slider_repeater['button_link'] ?? '/' . $slug . '/product-list') }}" class="btn-primary inline-block">
                                                        {{ $slider_repeater['button_text'] ?? __('Shop Now') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="swiper-pagination"></div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                            </div>
                            @if (isset($themeSettings['more_offer_status']) && $themeSettings['more_offer_status'] == 1)
                                <div class="mt-4 rounded-xl overflow-hidden border levo-offer-banner" style="border-color:#f0e4d8;">
                                    <a href="{{ $themeSettings['more_offer_button_link'] ?? route('page.product-list', ['storeSlug' => $slug]) }}">
                                        <img src="{{ get_file($themeSettings['more_offer_image']) }}" width="900" height="260" alt="offer" class="w-full h-auto object-cover" loading="lazy">
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <!-- logo Section — generic (niche-agnostic), hidden if no logos configured -->
        @if (!empty($themeSettings['logo_status']) && $themeSettings['logo_status'] == 1 && !empty($themeSettings['logo_repeater']))
        <section class="section-spacing">
            <div class="md:container mx-auto px-4 w-full">
                <!-- Swiper container -->
                <div class="swiper partnerSwiper pb-0 relative">
                    <div class="swiper-wrapper">
                        @foreach (json_decode($themeSettings['logo_repeater'],true) as $logo)
                                <div class="swiper-slide">
                                    <div class="partner-logo flex items-center justify-center bg-white rounded-xl h-full">
                                        <img
                                            src="{{ get_file($logo['image']) }}"
                                            width="150"
                                            height="150"
                                            alt="{{ $logo->alt_text ?? 'Partner Logo' }}"
                                            class="h-12 object-contain" loading="eager" fetchpriority="high" decoding="async"
                                        />
                                    </div>
                                </div>
                            @endforeach
                    </div>

                    <div class="arrow-wrapper">
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- Feature Collections Banner -->
        @if (!empty($themeSettings['collection_status']) && $themeSettings['collection_status'] == 1)
            <section class="section-spacing bg-gray-50">
                <div class="md:container mx-auto px-4 w-full">
                    <div class="grid md:grid-cols-2 gap-6">
                    @foreach (collect($MainCategoryList)->reverse()->take(2) as $category)
                                <div class="relative rounded-lg overflow-hidden group">
                                    <img src="{{ get_file($category->image_path)}}"
                                        alt="{{ $category->name }}"
                                        loading="lazy" width="200" height="200"
                                        class="w-full md:h-96 h-64 object-cover transition-transform duration-500 group-hover:scale-105 rtl:scale-x-[-1] rtl:group-hover:[--tw-scale-x:-1.05]"
                                    />
                                    <div
                                        class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-30 transition-all duration-300"
                                    ></div>
                                    <div class="absolute inset-0 flex items-end md:p-6 p-4">
                                        <div>
                                            <h3 class="text-white md:text-3xl text-2xl font-bold mb-2">
                                                {{ $category->name }}
                                            </h3>
                                            <p class="text-white mb-4 max-w-[200px] sm:max-w-[300px]">
                                                <!-- Optional description, if available -->
                                                {{ $category->description ?? 'Explore our latest ' . $category->name . ' collection.' }}
                                            </p>
                                            <a href="{{ route('collections.all', [$slug, $category->slug]) }}" class="btn-primary inline-block">
                                                {{__("Shop Collection")}}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Demo Category 2 — horizontal icon strip (10 cats), View All --}}
        @if (!empty($themeSettings['category_status']) && $themeSettings['category_status'] == 1)
            <section class="section-spacing" style="background: var(--levo-cream,#faf5ef);">
                <div class="md:container mx-auto px-4 w-full">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl md:text-2xl font-extrabold" style="color:var(--levo-leather,#1a1a1a); font-family:'Satoshi',sans-serif;">
                            {{ $themeSettings['category_title'] ?? __('Categories') }}
                        </h2>
                        <a href="{{ route('collections', $slug) }}" class="text-sm font-semibold flex items-center gap-1" style="color:var(--levo-primary,#e8007c);">
                            {{ __('View All') }} <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                        </a>
                    </div>
                    <div class="grid gap-3" style="grid-template-columns: repeat(5, minmax(0,1fr));">
                        @foreach (collect($MainCategoryList)->take(10) as $category)
                            <a href="{{ route('collections.all', [$slug, $category->slug]) }}" class="group flex flex-col items-center gap-2 p-3 bg-white rounded-xl border text-center hover:shadow-md transition" style="border-color:#f0e4d8;">
                                <div class="w-14 h-14 md:w-16 md:h-16 rounded-full overflow-hidden bg-[#f6efe7] border flex items-center justify-center" style="border-color:#f0e4d8;">
                                    <img src="{{ get_file($category->image_path) }}" alt="{{ $category->name }}" width="80" height="80" loading="lazy" class="w-full h-full object-cover" />
                                </div>
                                <span class="text-xs md:text-sm font-medium text-gray-800 line-clamp-1">{{ $category->name }}</span>
                            </a>
                        @endforeach
                    </div>
                    <style>@media (max-width: 767px){ .grid{ grid-template-columns: repeat(5, minmax(0,1fr)) !important; } } @media (max-width: 479px){ .grid{ grid-template-columns: repeat(4, minmax(0,1fr)) !important; } }</style>
                </div>
            </section>
        @endif

        {{-- Demo Tag sectors — New Arrival / Popular / Flash Sale (3 blocks, each with countdown + 4-col grid) --}}
        @if (!empty($themeSettings['bestseller_status']) && $themeSettings['bestseller_status'] == 1)
            @php
                $levoTags = collect($tags ?? [])->take(3);
                if ($levoTags->isEmpty()) $levoTags = collect([ (object)['name'=>'New Arrivals','products'=> collect($all_products)->take(8) ] ]);
            @endphp
            @foreach ($levoTags as $tag)
                <section class="section-spacing" style="background: var(--levo-cream,#faf5ef);">
                    <div class="md:container mx-auto px-4 w-full">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <h2 class="text-xl md:text-2xl font-extrabold" style="color:var(--levo-leather,#1a1a1a); font-family:'Satoshi',sans-serif;">{{ $tag->name ?? $tag['name'] ?? __('Products') }}</h2>
                                <div class="hidden sm:flex items-center gap-1 text-xs">
                                    <span class="px-2 py-1 rounded bg-white border" style="border-color:#f0e4d8;">00</span><span class="text-gray-500">H</span>
                                    <span class="px-2 py-1 rounded bg-white border" style="border-color:#f0e4d8;">31</span><span class="text-gray-500">M</span>
                                    <span class="px-2 py-1 rounded bg-white border" style="border-color:#f0e4d8;">25</span><span class="text-gray-500">S</span>
                                </div>
                            </div>
                            <a href="{{ route('page.product-list', $slug) }}" class="text-sm font-semibold flex items-center gap-1" style="color:var(--levo-primary,#e8007c);">{{ __('View All') }} <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></a>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                            @foreach (collect($tag->products ?? $tag['products'] ?? $all_products)->take(8) as $product)
                                <x-product-card :store="$store" :product="$product" />
                            @endforeach
                        </div>
                    </div>
                </section>
            @endforeach
        @endif

        <!-- Products Section (fallback when bestseller off) -->
        @if (!empty($themeSettings['product_status']) && $themeSettings['product_status'] == 1 && empty($themeSettings['bestseller_status']))
            <section class="section-spacing" style="background: var(--levo-cream,#faf5ef);">
                <div class="md:container mx-auto px-4 w-full">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl md:text-2xl font-extrabold" style="color:var(--levo-leather,#1a1a1a); font-family:'Satoshi',sans-serif;">{{ $themeSettings['product_title'] ?? __('Featured Products') }}</h2>
                        <a href="{{ route('page.product-list', $slug) }}" class="text-sm font-semibold" style="color:var(--levo-primary,#e8007c);">{{ __('View All') }}</a>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                        @foreach(collect($all_products)->reverse()->take(8) as $product)
                            <x-product-card :store="$store" :product="$product" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
        @include('front_end.hooks.product_list')
        <!-- Testimonials Section -->
        @if (!empty($themeSettings['testimonial_status']) && $themeSettings['testimonial_status'] == 1)
            <section class="section-spacing bg-gray-50">
                <div class="md:container mx-auto px-4 w-full">
                    <!-- Section Title -->
                    <div class="text-center md:mb-10 mb-6">
                        <h2 class="md:text-4xl text-3xl font-bold mb-4">
                            {!! $themeSettings['testimonial_title'] ?? 'What Our Customers Say' !!}
                        </h2>
                        <p class="text-gray-600 max-w-2xl mx-auto">
                            {!! $themeSettings['testimonial_sub_title'] ?? 'Read testimonials from our satisfied customers around the world.' !!}
                        </p>
                    </div>
                    <!-- Testimonials Slider -->
                    <div class="swiper testimonials-slider">
                        <div class="swiper-wrapper">
                            @foreach ($testimonials as $review)
                                <div class="swiper-slide">
                                    <div class="bg-white md:p-6 p-4 rounded-lg shadow-md h-full flex flex-col">
                                        <!-- Star Rating & Review -->
                                        <div class="flex-1">
                                            <div class="text-yellow-400 mb-4">
                                                @php
                                                    $stars = '';
                                                    $fullStars = floor($review->rating_no);
                                                    $halfStar = ($review->rating_no - $fullStars) >= 0.5;

                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $fullStars) {
                                                            $stars .= '<i class="fas fa-star"></i>';
                                                        } elseif ($i === $fullStars + 1 && $halfStar) {
                                                            $stars .= '<i class="fas fa-star-half-alt"></i>';
                                                        } else {
                                                            $stars .= '<i class="far fa-star"></i>';
                                                        }
                                                    }
                                                @endphp
                                                {!! $stars !!}
                                            </div>
                                            <p class="text-gray-600 mb-6">
                                                {!! $review->description ?? 'This customer did not leave a written review.' !!}
                                            </p>
                                        </div>

                                        <!-- Reviewer Info -->
                                        <div class="flex items-center gap-4">
                                            <img
                                            src="{{ get_file($review->avatar) }}"
                                                alt="Customer"
                                                class="w-12 h-12 rounded-full object-cover" loading="lazy" decoding="async"
                                            />
                                            <div>
                                                <h4 class="font-medium">
                                                    {{ $review->username ?? 'User' }}
                                                </h4>
                                                <p class="text-gray-500 text-sm">
                                                    {{ $review->productData?->name ?? 'Product Detail not provided' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Swiper Navigation -->
                        <div class="arrow-wrapper">
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                    </div>
                </div>
            </section>
        @endif                                         
        <!-- Blog Section -->
        @if (!empty($themeSettings['blog_status']) && $themeSettings['blog_status'] == 1)
            <section class="section-spacing">
            <div class="md:container mx-auto px-4 w-full">
                <div class="text-center md:mb-10 mb-6">
                    <h2 class="md:text-4xl text-3xl font-bold mb-4">{!! $themeSettings['blog_title'] ?? 'Fashion Blog' !!}</h2>
                    <p class="text-gray-600 max-w-2xl mx-auto">
                        {!! $themeSettings['blog_sub_title'] ?? 'Style tips, trend reports, and fashion inspiration' !!}
                    </p>
                </div>
                <div class="blog-slider swiper">
                <div class="swiper-wrapper">
                    @foreach ($blogs as $blog)
                    <div class="swiper-slide">
                     <x-blog-card :store="$store" :blog="$blog" />
                    </div>
                    @endforeach
                </div>
                <div class="arrow-wrapper">
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
                </div>
            </div>
            </section>
        @endif

        <!-- Service Section -->
        @if (isset($themeSettings['service_status']) && $themeSettings['service_status'] == 1)
            <section class="section-spacing pt-0">
                <div class="md:container mx-auto px-4 w-full">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 md:gap-6 gap-4">  
                        @foreach (json_decode($themeSettings['service_repeater'], true) as $service)
                            <div class="service-card bg-white rounded-xl p-4 border border-gray-100 flex items-center gap-4">
                                <div class="service-icon text-orange-500 floating">
                                    <img src="{{ get_file($service['image']) }}" alt="{{ $service['title'] }}" class="w-12 h-12 object-contain" loading="lazy">
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ $service['title'] }}</h3>
                                    <p class="text-gray-600">{{ $service['content'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </main>
@endsection

