@extends('front_end.layouts.app')

@section('page-title')
    {{ $store->name }} · {{ __('Shop') }}
@endsection

@section('content')
@php
    $heroSlides = json_decode($themeSettings['slider_repeater'] ?? '[]', true);
    $heroSlides = is_array($heroSlides) ? $heroSlides : [];
    $homeCategories = collect($MainCategoryList ?? []);
    $homeProducts = collect($all_products ?? []);
    $homeBrands = collect($brands ?? []);
@endphp
<main class="rc-container rc-home">
    <section class="rc-featured-layout" aria-label="{{ __('Featured offers') }}">
        <aside class="rc-featured-categories" aria-label="{{ __('Categories') }}">
            <h2>{{ __('Shop by category') }}</h2>
            <ul>
                @forelse ($homeCategories->take(8) as $category)
                    <li><a href="{{ url($slug . '/' . $category->slug) }}"><span class="rc-category-mark" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($category->name, 0, 1)) }}</span><span>{{ $category->name }}</span><span aria-hidden="true">›</span></a></li>
                @empty
                    <li><a href="{{ route('page.product-list', $slug) }}">{{ __('Browse all products') }} <span aria-hidden="true">›</span></a></li>
                @endforelse
            </ul>
            <a class="rc-text-link" href="{{ route('page.product-list', $slug) }}">{{ __('View all categories') }} <span aria-hidden="true">→</span></a>
        </aside>

        <div class="rc-featured-main">
            <div class="rc-hero" data-rc-carousel aria-roledescription="carousel" aria-label="{{ __('Store offers') }}">
                @if (count($heroSlides))
                    @foreach ($heroSlides as $index => $slide)
                        @php
                            $slideImage = !empty($slide['image'] ?? null) ? get_file($slide['image']) : asset('themes/readycart/assets/images/hero-watch.png');
                            $slideLink = $slide['button_link'] ?? route('page.product-list', $slug);
                        @endphp
                        <article class="rc-hero-slide {{ $index === 0 ? 'is-active' : '' }}" data-rc-slide @if($index !== 0) hidden @endif aria-label="{{ __('Offer :number of :total', ['number' => $index + 1, 'total' => count($heroSlides)]) }}">
                            <img src="{{ $slideImage }}" alt="" @if($index === 0) fetchpriority="high" @else loading="lazy" @endif>
                            <div class="rc-hero-copy">
                                <span class="rc-eyebrow">{{ $slide['small_text'] ?? __('Featured offer') }}</span>
                                <h1>{{ $slide['big_text'] ?? __('Find your next favorite') }}</h1>
                                <p>{{ $slide['content'] ?? __('Everyday essentials and standout picks, selected for your store.') }}</p>
                                <a class="rc-button rc-button--light" href="{{ $slideLink }}">{{ $slide['button_text'] ?? __('Shop now') }} <span aria-hidden="true">→</span></a>
                            </div>
                        </article>
                    @endforeach
                @else
                    <article class="rc-hero-slide is-active" data-rc-slide>
                        <img src="{{ asset('themes/readycart/assets/images/hero-watch.png') }}" alt="" fetchpriority="high">
                        <div class="rc-hero-copy">
                            <span class="rc-eyebrow">{{ __('New season') }}</span>
                            <h1>{{ __('Find your next favorite') }}</h1>
                            <p>{{ __('Everyday essentials and standout picks, selected for your store.') }}</p>
                            <a class="rc-button rc-button--light" href="{{ route('page.product-list', $slug) }}">{{ __('Shop now') }} <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                @endif
                <div class="rc-hero-controls">
                    <button type="button" data-rc-carousel-prev aria-label="{{ __('Previous offer') }}">←</button>
                    <div class="rc-hero-dots" role="group" aria-label="{{ __('Choose an offer') }}">
                        @foreach ($heroSlides ?: [0] as $index => $slide)
                            <button type="button" data-rc-carousel-dot="{{ $index }}" aria-label="{{ __('Show offer :number', ['number' => $index + 1]) }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"></button>
                        @endforeach
                    </div>
                    <button type="button" data-rc-carousel-next aria-label="{{ __('Next offer') }}">→</button>
                </div>
            </div>
            <div class="rc-promo-grid">
                <a class="rc-promo-card" href="{{ $themeSettings['more_offer_button_link'] ?? route('page.product-list', $slug) }}">
                    <img src="{{ !empty($themeSettings['more_offer_image'] ?? null) ? get_file($themeSettings['more_offer_image']) : asset('themes/readycart/assets/images/banner-beauty.png') }}" alt="" loading="lazy">
                    <span><small>{{ __('New arrivals') }}</small><strong>{{ $themeSettings['more_offer_title'] ?? __('Discover the latest') }}</strong></span>
                </a>
                <a class="rc-promo-card" href="{{ route('page.product-list', $slug) }}">
                    <img src="{{ asset('themes/readycart/assets/images/banner-outdoor.png') }}" alt="" loading="lazy">
                    <span><small>{{ __('Limited time') }}</small><strong>{{ __('Explore today’s offers') }}</strong></span>
                </a>
            </div>
        </div>
    </section>

    <section class="rc-benefits" aria-label="{{ __('Shopping benefits') }}">
        <div><span aria-hidden="true">✓</span><p><strong>{{ __('Secure payments') }}</strong><small>{{ __('Protected checkout') }}</small></p></div>
        <div><span aria-hidden="true">↗</span><p><strong>{{ __('Reliable delivery') }}</strong><small>{{ __('Orders tracked end to end') }}</small></p></div>
        <div><span aria-hidden="true">◇</span><p><strong>{{ __('Quality picks') }}</strong><small>{{ __('Selected for your store') }}</small></p></div>
        <div><span aria-hidden="true">↺</span><p><strong>{{ __('Easy returns') }}</strong><small>{{ __('Support when you need it') }}</small></p></div>
    </section>

    @if ($homeCategories->isNotEmpty())
    <section class="rc-section" aria-labelledby="rc-categories-title">
        <div class="rc-section-heading"><div><span class="rc-kicker">{{ __('Find what you need') }}</span><h2 id="rc-categories-title">{{ $themeSettings['category_title'] ?? __('Shop by category') }}</h2></div><a href="{{ route('page.product-list', $slug) }}">{{ __('View all') }} <span aria-hidden="true">→</span></a></div>
        <div class="rc-category-grid">
            @foreach ($homeCategories->take(8) as $category)
                <a class="rc-category-card" href="{{ url($slug . '/' . $category->slug) }}">
                    <span class="rc-category-card__image"><img src="{{ get_file($category->image_path ?? $category->icon_path ?? '') }}" alt="" loading="lazy"></span>
                    <span>{{ $category->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    @if (($themeSettings['product_status'] ?? 1) == 1)
    <section class="rc-section" aria-labelledby="rc-featured-title">
        <div class="rc-section-heading"><div><span class="rc-kicker">{{ __('Picked for you') }}</span><h2 id="rc-featured-title">{{ $themeSettings['product_title'] ?? __('Featured products') }}</h2></div><a href="{{ route('page.product-list', $slug) }}">{{ __('View all') }} <span aria-hidden="true">→</span></a></div>
        @if (!empty($themeSettings['product_description']))<p class="rc-section-description">{{ $themeSettings['product_description'] }}</p>@endif
        <div class="rc-product-grid">
            @forelse ($homeProducts->take(6) as $product)
                <x-product-card :store="$store" :product="$product" />
            @empty
                <p class="rc-empty-state">{{ __('Products will appear here when they are available in this store.') }}</p>
            @endforelse
        </div>
    </section>
    @endif

    <section class="rc-wide-promo" aria-label="{{ __('Shop today') }}">
        <img src="{{ asset('themes/readycart/assets/images/banner-outdoor.png') }}" alt="" loading="lazy">
        <div><span class="rc-eyebrow">{{ __('A better way to shop') }}</span><h2>{{ __('Good finds, all in one place') }}</h2><a class="rc-button rc-button--light" href="{{ route('page.product-list', $slug) }}">{{ __('Explore products') }} <span aria-hidden="true">→</span></a></div>
    </section>

    @if ($homeBrands->isNotEmpty())
    <section class="rc-section" aria-labelledby="rc-brands-title">
        <div class="rc-section-heading"><div><span class="rc-kicker">{{ __('Quality you know') }}</span><h2 id="rc-brands-title">{{ __('Shop top brands') }}</h2></div></div>
        <div class="rc-brand-grid">
            @foreach ($homeBrands->take(6) as $brand)
                <a class="rc-brand-card" href="{{ route('page.product-list', ['storeSlug' => $slug, 'product_brand' => data_get($brand, 'id', '')]) }}"><span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(data_get($brand, 'name', is_string($brand) ? $brand : __('Brand')), 0, 1)) }}</span><strong>{{ data_get($brand, 'name', is_string($brand) ? $brand : __('Brand')) }}</strong></a>
            @endforeach
        </div>
    </section>
    @endif
</main>
@endsection
