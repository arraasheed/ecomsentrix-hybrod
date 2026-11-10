@props(['product', 'store', 'layout' => 'grid'])

@php
    $productImage = get_file($product->cover_image_path ?? '');
    $productUrl = url($store->slug . '/product/' . $product->slug);
@endphp
<article class="rc-product-card {{ $layout === 'list' ? 'rc-product-card--list' : '' }}">
    {!! \App\Models\Product::productSalesPage($store->slug, $product->id, false) !!}
    <div class="rc-product-card__media">
        <a href="{{ $productUrl }}" aria-label="{{ __('View :product', ['product' => $product->name]) }}">
            <img src="{{ $productImage }}" alt="{{ $product->name }}" width="400" height="400" loading="lazy" decoding="async">
        </a>
        @if (!empty($product->label))
            <span class="rc-product-card__badge">{{ ucfirst(optional($product->label)->name ?? '') }}</span>
        @endif
        <button type="button" class="rc-wishlist-button wishlist-btn wishbtn-globaly {{ $product->in_whishlist ? 'active' : '' }}" product_id="{{ $product->id }}" data-product-id="{{ $product->id }}" data-wishlist-action="{{ $product->in_whishlist ? 'remove' : 'add' }}" in_wishlist="{{ $product->in_whishlist ? 'remove' : 'add' }}" aria-label="{{ $product->in_whishlist ? __('Remove from wishlist') : __('Add to wishlist') }}" aria-pressed="{{ $product->in_whishlist ? 'true' : 'false' }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 8.8c0 5.2-8.8 10.2-8.8 10.2S3.2 14 3.2 8.8a4.7 4.7 0 0 1 8.8-2.3 4.7 4.7 0 0 1 8.8 2.3Z"/></svg>
        </button>
        {!! \App\Models\Product::actionLinks($store, $product) !!}
    </div>
    <div class="rc-product-card__content">
        <p class="rc-product-card__category">{{ optional($product->category)->name ?? __('Featured') }}</p>
        <h3><a href="{{ $productUrl }}">{{ $product->name }}</a></h3>
        <p class="rc-product-card__description">{{ \Illuminate\Support\Str::limit(strip_tags($product->description ?? ''), 88) }}</p>
        <div class="rc-product-card__price">
            @if (($product->variant_product ?? 0) == 0)
                <span>{!! \App\Models\Product::getProductPrice($product, $store) !!}</span>
            @else
                <span>{{ __('From') }} {!! \App\Models\Product::getProductPrice($product, $store) !!}</span>
            @endif
        </div>
        <div class="rc-product-card__actions">
            <button type="button" class="rc-add-button addtocart-btn btn addcart-btn-globaly" product_id="{{ $product->id }}" variant_id="{{ $product->default_variant_id ?? 0 }}" qty="1" data-product-id="{{ $product->id }}" data-variant-id="{{ $product->default_variant_id ?? 0 }}" data-quantity="1" data-url="{{ route('add.to.cart', [$store->slug, $product->id]) }}">
                <span>{{ __('Add to cart') }}</span>
            </button>
            {!! \App\Models\Product::ProductcardButton($store->slug, $product) !!}
        </div>
    </div>
</article>
