@props(['product', 'store' => null, 'layout' => 'grid'])
@php
    $storeSlug = $slug ?? ($store->slug ?? request()->route('storeSlug') ?? 'stylique');
    $isDemo = is_array($product) ? (bool) ($product['is_demo'] ?? false) : false;
    $id = (int) data_get($product, 'id', 1);
    $name = (string) data_get($product, 'name', 'Product');
    $prodSlug = (string) data_get($product, 'slug', '');
    $brand = (string) (data_get($product, 'brand.name') ?: data_get($product, 'brand', 'ReadyCart'));
    $category = (string) (data_get($product, 'ProductData.name') ?: data_get($product, 'category', 'Featured'));

    if (is_array($product) && isset($product['price'])) {
        $price = (float) $product['price'];
        $oldPrice = (float) ($product['oldPrice'] ?? $price);
        $image = (string) ($product['image'] ?? asset('readycart/p-heater.png'));
        $rating = (float) ($product['rating'] ?? 4.8);
        $reviews = (int) ($product['reviews'] ?? 12);
        $sold = (int) ($product['sold'] ?? 24);
        $badge = $product['badge'] ?? null;
    } else {
        $price = (float) (data_get($product, 'sale_price') ?: data_get($product, 'final_price') ?: data_get($product, 'price', 0));
        $oldPrice = (float) (data_get($product, 'price') ?: $price);
        if ($oldPrice < $price) {
            $oldPrice = $price;
        }
        $rawImg = (string) data_get($product, 'cover_image_path', '');
        if (empty($rawImg) || str_contains($rawImg, 'themes/stylique/assets/images/service') || str_starts_with($name, 'Test ')) {
            $fallbackImgs = ['p-heater.png', 'p-fan.png', 'p-necklace.png', 'p-coat.png', 'p-firstaid.png', 'p-serum.png', 'p-ring.png', 'p-helmet.png', 'p-skates.png', 'p-gel.png'];
            $image = asset('readycart/' . $fallbackImgs[abs($id) % count($fallbackImgs)]);
        } else {
            $image = get_file($rawImg);
        }
        $rating = (float) (data_get($product, 'average_rating') ?: 4.8);
        $reviews = (int) (data_get($product, 'review_count') ?: 12);
        $sold = 14 + (abs($id) * 5);
        $disc = ($oldPrice > $price && $oldPrice > 0) ? (int) round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $badge = $disc > 0 ? ($disc . '% OFF') : null;
    }

    if ($isDemo || empty($prodSlug)) {
        $productUrl = route('landing_page', $storeSlug) . '?rc_product=' . $id;
    } else {
        $productUrl = route('page.product', ['storeSlug' => $storeSlug, 'product_slug' => $prodSlug]);
    }
    $checkoutUrl = route('landing_page', $storeSlug) . '?rc_checkout=1';
    $isList = $layout === 'list';
    $inWishlist = (bool) data_get($product, 'in_whishlist', false);
@endphp

<article
    data-rc-card
    data-product-id="{{ $id }}"
    data-product-name="{{ $name }}"
    data-product-brand="{{ $brand }}"
    data-product-category="{{ $category }}"
    data-product-price="{{ number_format($price, 2, '.', '') }}"
    data-product-image="{{ $image }}"
    data-product-url="{{ $productUrl }}"
    class="group relative flex rounded-lg border border-line bg-white p-2 transition duration-200 hover:shadow-[0_10px_24px_-14px_rgba(28,27,34,0.28)] {{ $isList ? 'flex-row gap-4' : 'h-full flex-col' }}"
>
    <div class="relative shrink-0 overflow-hidden rounded-md bg-surface {{ $isList ? 'h-40 w-40 sm:w-52' : 'h-40 w-full sm:h-44' }}">
        <a href="{{ $productUrl }}" class="absolute inset-0 block" aria-label="View {{ $name }}">
            <img
                src="{{ $image }}"
                alt="{{ $name }}"
                width="400"
                height="400"
                loading="lazy"
                class="h-full w-full object-contain p-2 mix-blend-multiply transition duration-300 group-hover:scale-105"
            />
        </a>
        @if (!empty($badge))
            <span class="absolute left-1.5 top-1.5 rounded-sm bg-brand px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-foreground">
                {{ $badge }}
            </span>
        @endif
        <button
            type="button"
            data-rc-wish-toggle="{{ $id }}"
            product_id="{{ $id }}"
            in_wishlist="{{ $inWishlist ? 'remove' : 'add' }}"
            aria-pressed="{{ $inWishlist ? 'true' : 'false' }}"
            aria-label="{{ $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' }}"
            class="wishbtn-globaly absolute right-1.5 top-1.5 flex size-7 items-center justify-center rounded-md bg-white/90 text-ink/60 shadow-sm transition hover:text-brand"
        >
            <svg class="size-3.5 {{ $inWishlist ? 'fill-brand text-brand' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
            </svg>
        </button>
    </div>

    <div class="flex flex-1 flex-col {{ $isList ? 'justify-center py-1 pr-1' : 'px-0.5 pt-2.5' }}">
        <a
            href="{{ $productUrl }}"
            class="text-left text-sm font-medium leading-snug text-ink transition hover:text-brand {{ $isList ? 'text-base' : 'line-clamp-1' }}"
        >
            {{ $name }}
        </a>
        <div class="mt-1 flex items-center gap-1 text-[11px] text-ink/55">
            <svg class="size-3 fill-amber-400 text-amber-400" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            <span class="font-semibold text-ink">{{ number_format($rating, 1) == (int) $rating ? (int) $rating : number_format($rating, 1) }}</span>
            <span>({{ $reviews }})</span>
            <span aria-hidden="true" class="text-ink/25">&middot;</span>
            <span>{{ $sold }} Sold</span>
        </div>
        <div class="mt-1.5 flex items-baseline gap-1.5">
            <span class="text-sm font-bold text-brand">${{ number_format($price, 2) }}</span>
            @if ($oldPrice > $price)
                <span class="text-[11px] text-ink/40 line-through">${{ number_format($oldPrice, 2) }}</span>
            @endif
        </div>
        <div class="flex gap-1.5 {{ $isList ? 'mt-4 max-w-xs' : 'mt-auto pt-2.5' }}">
            <button
                type="button"
                data-rc-add-cart="{{ $id }}"
                product_id="{{ $id }}"
                variant_id="0"
                qty="1"
                aria-label="Add {{ $name }} to cart"
                class="{{ $isDemo ? '' : 'addcart-btn-globaly' }} flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-surface text-ink/70 transition hover:bg-brand-soft hover:text-brand"
            >
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                </svg>
            </button>
            <button
                type="button"
                data-rc-buy-now="{{ $id }}"
                data-checkout-url="{{ $checkoutUrl }}"
                class="h-9 flex-1 rounded-md bg-brand text-xs font-semibold text-brand-foreground transition hover:bg-brand-dark"
            >
                Buy Now
            </button>
        </div>
    </div>
</article>

