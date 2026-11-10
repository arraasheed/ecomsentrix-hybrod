@extends('front_end.layouts.app')

@section('page-title')
    {{ $product->name }}
@endsection

@section('content')
@php
    $productImages = $product->Sub_image($product->id)['data'] ?? [];
    $productImages = collect($productImages);
    $relatedProducts = collect($all_products ?? [])->where('id', '!=', $product->id)->take(4);
    $isOutOfStock = ($product->track_stock ?? 0) == 0 && ($product->stock_status ?? '') === 'out_of_stock';
@endphp
<main class="rc-container rc-product-detail">
    <nav class="rc-breadcrumbs" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('landing_page', $slug) }}">{{ __('Home') }}</a><span aria-hidden="true">›</span>
        <a href="{{ route('page.product-list', $slug) }}">{{ __('Products') }}</a><span aria-hidden="true">›</span>
        <span aria-current="page">{{ $product->name }}</span>
    </nav>

    @if (($themeSettings['product_page_status'] ?? 1) == 1)
    <section class="rc-detail-grid" aria-labelledby="rc-product-title">
        <div class="rc-product-gallery">
            <div class="rc-gallery-main">
                @if ($productImages->isNotEmpty())
                    @foreach ($productImages as $index => $image)
                        <img data-rc-gallery-image src="{{ get_file($image->image_path) }}" alt="{{ $product->name }}{{ $index ? ' · ' . __('image :number', ['number' => $index + 1]) : '' }}" @if($index !== 0) hidden @endif @if($index === 0) fetchpriority="high" @else loading="lazy" @endif>
                    @endforeach
                @else
                    <img data-rc-gallery-image src="{{ get_file($product->cover_image_path) }}" alt="{{ $product->name }}" fetchpriority="high">
                @endif
            </div>
            @if ($productImages->isNotEmpty())
            <div class="rc-gallery-thumbs" aria-label="{{ __('Product images') }}">
                @foreach ($productImages as $index => $image)
                    <button type="button" data-rc-gallery-thumb="{{ $index }}" data-rc-gallery-src="{{ get_file($image->image_path) }}" class="{{ $index === 0 ? 'is-active' : '' }}" aria-label="{{ __('Show product image :number', ['number' => $index + 1]) }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}">
                        <img src="{{ get_file($image->image_path) }}" alt="" loading="lazy">
                    </button>
                @endforeach
            </div>
            @endif
        </div>

        <div class="rc-detail-info">
            @if (!empty($product->label))<p class="rc-product-brand">{{ optional($product->label)->name }}</p>@endif
            <h1 id="rc-product-title">{{ $product->name }}</h1>
            @if (!empty($product->description))<div class="rc-detail-description">{{ \Illuminate\Support\Str::limit(strip_tags($product->description), 180) }}</div>@endif
            <div class="rc-detail-price product-price-amount" data-rc-product-price>{!! \App\Models\Product::getProductPrice($product, $store) !!}</div>
            <p class="rc-stock {{ $isOutOfStock ? 'is-out' : '' }}" data-rc-stock data-in-stock-label="{{ __('In stock') }}" data-out-of-stock-label="{{ __('Out of stock') }}" product_id="{{ $product->id }}" variant_id="{{ $product->default_variant_id ?? 0 }}" qty="1">
                {{ $isOutOfStock ? __('Out of stock') : __('In stock') }}
            </p>

            <form class="variant_form rc-variant-form" data-island="variant-selector" data-prop-slug="{{ $slug }}" data-prop-product-id="{{ $product->id }}" data-rc-price-url="{{ url('/product-price/' . $product->id) }}">
                <input type="hidden" name="variant_id" value="{{ $product->default_variant_id ?? 0 }}" data-rc-selected-variant>
                @include('front_end.common.product.variant')
                <div class="rc-quantity-row">
                    <label for="product-quantity">{{ __('Quantity') }}</label>
                    <div class="rc-quantity-control">
                        <button type="button" class="quantity-decrement change_price" data-product="{{ $product->id }}" aria-label="{{ __('Decrease quantity') }}">−</button>
                        <input id="product-quantity" class="quantity product-quantity" name="quantity" value="1" min="1" max="10" data-product="{{ $product->id }}" data-rc-quantity inputmode="numeric" aria-label="{{ __('Quantity') }}">
                        <button type="button" class="quantity-increment change_price" data-product="{{ $product->id }}" aria-label="{{ __('Increase quantity') }}">+</button>
                    </div>
                </div>
            </form>

            <div class="rc-detail-actions">
                <button type="button" class="rc-button rc-button--primary addtocart-btn btn addcart-btn-globaly price-wise-btn product_var_option" product_id="{{ $product->id }}" variant_id="{{ $product->default_variant_id ?? 0 }}" qty="1" data-product-id="{{ $product->id }}" data-variant-id="{{ $product->default_variant_id ?? 0 }}" data-quantity="1" data-url="{{ route('product.cart', [$slug]) }}" @if($isOutOfStock) disabled aria-disabled="true" @endif><span>{{ __('Add to cart') }}</span></button>
                @if (!$isOutOfStock)
                    {!! \App\Models\Product::ProductcardButton($slug, $product) !!}
                @endif
                <button type="button" class="rc-button rc-button--outline wishbtn-globaly" product_id="{{ $product->id }}" data-product-id="{{ $product->id }}" data-wishlist-action="{{ $product->in_whishlist ? 'remove' : 'add' }}" in_wishlist="{{ $product->in_whishlist ? 'remove' : 'add' }}" aria-pressed="{{ $product->in_whishlist ? 'true' : 'false' }}">{{ $product->in_whishlist ? __('Saved') : __('Save item') }}</button>
            </div>

            <ul class="rc-trust-list">
                <li>✓ {{ __('Genuine product') }}</li>
                <li>↗ {{ __('Reliable delivery') }}</li>
                <li>↺ {{ __('Easy returns') }}</li>
                <li>◇ {{ __('Secure checkout') }}</li>
            </ul>
            <div class="rc-product-hooks">@include('front_end.hooks.product_detail_info_button')</div>
        </div>
    </section>

    <section class="rc-product-description" aria-labelledby="rc-description-title">
        <h2 id="rc-description-title">{{ __('Product details') }}</h2>
        <div>{!! $product->description !!}</div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="rc-related-products" aria-labelledby="rc-related-title">
            <div class="rc-section-heading"><div><span class="rc-kicker">{{ __('More to explore') }}</span><h2 id="rc-related-title">{{ __('You may also like') }}</h2></div><a href="{{ route('page.product-list', $slug) }}">{{ __('View all') }} →</a></div>
            <div class="rc-product-grid">
                @foreach ($relatedProducts as $relatedProduct)
                    <x-product-card :store="$store" :product="$relatedProduct" />
                @endforeach
            </div>
        </section>
    @endif
    @endif
</main>
@endsection
