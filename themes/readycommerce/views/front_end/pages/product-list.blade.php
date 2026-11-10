@extends('front_end.layouts.app')

@section('page-title')
    {{ $themeSettings['product_list_banner_title'] ?? __('Products') }}
@endsection

@section('content')
@php
    $listProducts = collect($products ?? []);
    $filterTags = collect($filter_tag ?? []);
    $brandFilters = collect($brands ?? []);
@endphp
<main class="rc-container rc-page rc-products-page" data-rc-products data-filter-url="{{ route('product.page.filter', $slug) }}" data-store-slug="{{ $slug }}">
    @if (($themeSettings['product_list_banner_status'] ?? 0) == 1)
        <section class="rc-page-banner" @if(!empty($themeSettings['product_list_banner_image'])) style="--rc-banner-image: url('{{ get_file($themeSettings['product_list_banner_image']) }}')" @endif>
            <span class="rc-kicker">{{ __('Browse the collection') }}</span>
            <h1>{{ $themeSettings['product_list_banner_title'] ?? __('All products') }}</h1>
        </section>
    @else
        <header class="rc-page-heading"><div><span class="rc-kicker">{{ __('Browse the collection') }}</span><h1>{{ __('All products') }}</h1><p>{{ __('Find the right product for every day.') }}</p></div></header>
    @endif

    <div class="rc-products-layout">
        <button type="button" class="rc-filter-backdrop" id="rc-filter-backdrop" data-rc-filter-close aria-label="{{ __('Close filters') }}"></button>
        <aside class="rc-filter-panel" id="mobile-filter" aria-label="{{ __('Product filters') }}" aria-hidden="true">
            <div class="rc-filter-panel__heading"><h2>{{ __('Filter products') }}</h2><button type="button" id="filter-close" data-rc-filter-close aria-label="{{ __('Close filters') }}">×</button></div>
            <div class="rc-filter-group">
                <h3>{{ __('Categories') }}</h3>
                @foreach ($filterTags as $category)
                    <label class="rc-check-row" for="category-{{ $category->id }}"><input type="checkbox" id="category-{{ $category->id }}" class="product_tag" value="{{ $category->id }}" @checked(in_array($category->id, $category_ids ?? []))><span>{{ $category->name }}</span></label>
                @endforeach
            </div>
            <div class="rc-filter-group">
                <label for="priceRangeSlider"><strong>{{ __('Maximum price') }}</strong></label>
                <input type="range" id="priceRangeSlider" min="{{ $min_price ?? 0 }}" max="{{ $max_price ?? 1000 }}" value="{{ $selected_max_price ?? $max_price ?? 1000 }}" step="1">
                <div class="rc-price-range"><span id="min_price_select">{{ currency_format_with_sym($min_price ?? 0, $store->id) }}</span><span id="sliderValue">{{ currency_format_with_sym($selected_max_price ?? $max_price ?? 1000, $store->id) }}</span></div>
                <input type="hidden" id="maxPriceInput" value="{{ $selected_max_price ?? $max_price ?? 1000 }}">
            </div>
            <div class="rc-filter-group">
                <h3>{{ __('Brands') }}</h3>
                @foreach ($brandFilters as $brand)
                    <label class="rc-check-row" for="brand-{{ $brand->id }}"><input type="checkbox" id="brand-{{ $brand->id }}" class="product_brand" value="{{ $brand->id }}" @checked(in_array($brand->id, $brand_select ?? []))><span>{{ $brand->name }}</span></label>
                @endforeach
            </div>
            <button type="button" id="product_filter_btn" class="rc-button rc-button--primary rc-button--full">{{ __('Apply filters') }}</button>
        </aside>

        <section class="rc-product-results" aria-label="{{ __('Products') }}">
            <div class="rc-results-toolbar">
                <div><p class="rc-results-count"><strong><span class="product_count">{{ $product_count ?? $listProducts->count() }}</span></strong> {{ __('products') }}</p></div>
                <div class="rc-results-controls">
                    <button type="button" id="filter-icon" data-rc-filter-open aria-controls="mobile-filter" aria-expanded="false" class="rc-button rc-button--outline rc-filter-trigger">{{ __('Filters') }}</button>
                    <div class="rc-view-toggle" role="group" aria-label="{{ __('Product view') }}">
                        <button type="button" id="grid-view-btn" class="view-toggle is-active" aria-pressed="true" aria-label="{{ __('Grid view') }}">▦</button>
                        <button type="button" id="list-view-btn" class="view-toggle" aria-pressed="false" aria-label="{{ __('List view') }}">☷</button>
                    </div>
                    <label class="sr-only" for="filter_product">{{ __('Sort products') }}</label>
                    <select id="filter_product" class="filter_product rc-select">
                        <option value="all" @selected(empty($filter_product) || $filter_product === 'all')>{{ __('All products') }}</option>
                        <option value="manual">{{ __('Featured') }}</option>
                        <option value="best-selling" @selected(($filter_product ?? '') === 'best-selling')>{{ __('Best selling') }}</option>
                        <option value="title-ascending">{{ __('Name: A–Z') }}</option>
                        <option value="title-descending">{{ __('Name: Z–A') }}</option>
                        <option value="price-ascending">{{ __('Price: low to high') }}</option>
                        <option value="price-descending">{{ __('Price: high to low') }}</option>
                        <option value="created-descending">{{ __('Newest') }}</option>
                    </select>
                </div>
            </div>
            <div class="product_filter rc-product-filter-results" aria-live="polite">
                <div id="products-container" class="rc-product-grid">
                    @forelse ($listProducts as $product)
                        <x-product-card :store="$store" :product="$product" />
                    @empty
                        <p class="rc-empty-state">{{ __('No products match these filters.') }}</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</main>
@endsection
