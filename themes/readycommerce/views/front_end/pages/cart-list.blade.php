@php
    $cartData = $response->data ?? null;
    $cartLines = collect($cartData->product_list ?? []);
    $cartSubtotal = (float) ($cartData->final_price ?? $cartData->sub_total ?? 0);
    $cartTax = (float) ($cartData->tax_price ?? 0);
    $cartTotal = (float) (($cartData->sub_total ?? $cartSubtotal) + $cartTax);
@endphp
<div class="rc-cart-layout">
    <section class="rc-card-panel" aria-labelledby="rc-cart-items-title">
        <h2 id="rc-cart-items-title">{{ __('Items in your cart') }} <span class="product_count">({{ $cartData->cart_total_product ?? $cartLines->count() }})</span></h2>
        @if ($cartLines->isNotEmpty())
            <div class="rc-cart-table-wrap">
                <table class="rc-cart-table">
                    <thead><tr><th>{{ __('Product') }}</th><th>{{ __('Quantity') }}</th><th>{{ __('Price') }}</th><th>{{ __('Subtotal') }}</th></tr></thead>
                    <tbody>
                    @foreach ($cartLines as $line)
                        <tr>
                            <td>
                                <div class="rc-cart-item">
                                    <button type="button" class="rc-cart-remove remove_item_from_cart" title="{{ __('Remove item') }}" aria-label="{{ __('Remove :product', ['product' => $line->name]) }}" data-id="{{ $line->cart_id }}" data-product-id="{{ $line->product_id }}" data-variant-id="{{ $line->variant_id }}">Ã—</button>
                                    <a href="{{ url($slug . '/product/' . getProductSlug($line->product_id)) }}"><img src="{{ get_file($line->image) }}" alt="{{ $line->name }}" loading="lazy"></a>
                                    <div><a href="{{ url($slug . '/product/' . getProductSlug($line->product_id)) }}"><strong>{{ $line->name }}</strong></a>@if(($line->variant_id ?? 0) != 0)<p>{!! \App\Models\ProductVariant::variantlist($line->variant_id) !!}</p>@endif</div>
                                </div>
                            </td>
                            <td>
                                <div class="rc-quantity-control">
                                    <button type="button" class="quantity-decrement change-cart-globaly" aria-label="{{ __('Decrease quantity') }}" cart-id="{{ $line->cart_id }}" quantity_type="decrease" data-product-id="{{ $line->product_id }}" data-variant-id="{{ $line->variant_id }}">âˆ’</button>
                                    <input class="quantity" type="text" value="{{ $line->qty }}" min="1" inputmode="numeric" aria-label="{{ __('Quantity for :product', ['product' => $line->name]) }}">
                                    <button type="button" class="quantity-increment change-cart-globaly" aria-label="{{ __('Increase quantity') }}" cart-id="{{ $line->cart_id }}" quantity_type="increase" data-product-id="{{ $line->product_id }}" data-variant-id="{{ $line->variant_id }}">+</button>
                                </div>
                            </td>
                            {!! \App\Models\Product::ManageCartListPrice($line, $store) !!}
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="rc-cart-actions">
                <a class="rc-text-link" href="{{ route('page.product-list', $store->slug) }}">â† {{ $themeSettings['cart_product_button_text'] ?? __('Continue shopping') }}</a>
                <button type="button" class="empty_cart">{{ $themeSettings['cart_clear_button_text'] ?? __('Clear cart') }}</button>
            </div>
        @else
            <div class="rc-empty-state">{{ __('Your cart is empty.') }}<p><a class="rc-button rc-button--primary" href="{{ route('page.product-list', $store->slug) }}">{{ __('Explore products') }}</a></p></div>
        @endif
    </section>

    <aside class="rc-card-panel rc-cart-summary" aria-labelledby="rc-cart-summary-title">
        <h2 id="rc-cart-summary-title">{{ $themeSettings['cart_summary'] ?? __('Order summary') }}</h2>
        <div class="rc-summary-row"><span>{{ $themeSettings['cart_subtotal'] ?? __('Subtotal') }}</span><strong>{{ currency_format_with_sym($cartSubtotal, $store->id) }}</strong></div>
        @if ($cartTax > 0)<div class="rc-summary-row"><span>{{ $themeSettings['cart_tax'] ?? __('Tax') }}</span><strong>{{ currency_format_with_sym($cartTax, $store->id) }}</strong></div>@endif
        <div class="rc-summary-row rc-summary-total"><span>{{ $themeSettings['cart_total'] ?? __('Total') }}</span><strong>{{ currency_format_with_sym($cartTotal, $store->id) }}</strong></div>
        @if ($cartLines->isNotEmpty())
            @php $checkoutLoginRequired = \App\Models\Utility::GetValueByName('is_checkout_login_required', $store->id); @endphp
            @if ($checkoutLoginRequired === 'on' && !auth('customers')->user())
                <a class="rc-button rc-button--primary rc-button--full" href="{{ route('customer.login', $store->slug) }}">{{ __('Sign in to checkout') }}</a>
            @else
                <a class="rc-button rc-button--primary rc-button--full" href="{{ route('checkout', $slug) }}">{{ $themeSettings['cart_checkout'] ?? __('Proceed to checkout') }}</a>
            @endif
        @endif
    </aside>
</div>

