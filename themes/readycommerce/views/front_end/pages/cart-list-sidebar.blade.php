@php
    $drawerCart = $response->data ?? null;
    $drawerProducts = collect($drawerCart->product_list ?? []);
    $drawerTotal = (float) ($drawerCart->sub_total ?? $drawerCart->final_price ?? 0) + (float) ($drawerCart->tax_price ?? 0);
@endphp
<div id="cart-body" class="rc-cart-drawer-content" data-rc-cart-fragment>
    <div class="rc-card-panel" style="display:flex;align-items:center;justify-content:space-between;border:0;border-bottom:1px solid var(--rc-line);border-radius:0">
        <h2 style="margin:0;font-size:16px">{{ __('Your cart') }} <span>(<span data-rc-cart-count>{{ $drawerCart->cart_total_product ?? $drawerProducts->count() }}</span>)</span></h2>
        <button type="button" id="cart-close" aria-label="{{ __('Close cart') }}">×</button>
    </div>
    <div class="rc-cart-drawer-content__items">
        @forelse ($drawerProducts as $line)
            <article class="rc-cart-drawer-item">
                <a href="{{ url($slug . '/product/' . getProductSlug($line->product_id)) }}"><img src="{{ get_file($line->image) }}" alt="{{ $line->name }}" loading="lazy"></a>
                <div style="min-width:0;flex:1">
                    <div style="display:flex;align-items:start;justify-content:space-between;gap:8px">
                        <a href="{{ url($slug . '/product/' . getProductSlug($line->product_id)) }}"><strong>{{ $line->name }}</strong></a>
                        <button type="button" class="rc-cart-remove remove_item_from_cart" aria-label="{{ __('Remove item') }}" data-id="{{ $line->cart_id }}" data-product-id="{{ $line->product_id }}" data-variant-id="{{ $line->variant_id }}">×</button>
                    </div>
                    @if (($line->variant_id ?? 0) != 0)<p>{!! \App\Models\ProductVariant::variantlist($line->variant_id) !!}</p>@endif
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:10px">
                        <div class="rc-quantity-control">
                            <button type="button" class="quantity-decrement change-cart-globaly" aria-label="{{ __('Decrease quantity') }}" cart-id="{{ $line->cart_id }}" quantity_type="decrease" data-product-id="{{ $line->product_id }}" data-variant-id="{{ $line->variant_id }}">−</button>
                            <input class="quantity" type="text" value="{{ $line->qty }}" min="1" inputmode="numeric" aria-label="{{ __('Quantity for :product', ['product' => $line->name]) }}">
                            <button type="button" class="quantity-increment change-cart-globaly" aria-label="{{ __('Increase quantity') }}" cart-id="{{ $line->cart_id }}" quantity_type="increase" data-product-id="{{ $line->product_id }}" data-variant-id="{{ $line->variant_id }}">+</button>
                        </div>
                        <strong>{!! \App\Models\Product::ManageCartPrice($line, $store) !!}</strong>
                    </div>
                </div>
            </article>
        @empty
            <p class="rc-empty-state">{{ __('Your cart is empty.') }}</p>
        @endforelse
    </div>
    <div class="rc-card-panel" style="border:0;border-top:1px solid var(--rc-line);border-radius:0">
        <div class="rc-summary-row"><span>{{ __('Subtotal') }}</span><strong data-rc-cart-subtotal>{{ currency_format_with_sym((float) ($drawerCart->sub_total ?? $drawerCart->final_price ?? 0), $store->id) }}</strong></div>
        @if ((float) ($drawerCart->tax_price ?? 0) > 0)<div class="rc-summary-row"><span>{{ __('Tax') }}</span><strong data-rc-cart-tax>{{ currency_format_with_sym((float) $drawerCart->tax_price, $store->id) }}</strong></div>@endif
        <div class="rc-summary-row rc-summary-total"><span>{{ __('Total') }}</span><strong data-rc-cart-total>{{ currency_format_with_sym($drawerTotal, $store->id) }}</strong></div>
        <a class="rc-button rc-button--primary rc-button--full" href="{{ route('page.cart', $slug) }}">{{ __('View cart') }}</a>
        @if ($drawerProducts->isNotEmpty())<a class="rc-button rc-button--outline rc-button--full" style="margin-top:8px" href="{{ route('checkout', $slug) }}">{{ __('Proceed to checkout') }}</a>@endif
    </div>
</div>
