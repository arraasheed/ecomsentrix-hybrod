@php
    $mobileCartCount = \App\Models\Cart::CartCount($slug ?? $store->slug);
@endphp
<nav class="rc-mobile-nav" aria-label="{{ __('Mobile navigation') }}">
    <a href="{{ route('landing_page', $slug) }}" @if(request()->routeIs('landing_page')) aria-current="page" @endif>
        <span aria-hidden="true">⌂</span><span>{{ __('Home') }}</span>
    </a>
    <a href="{{ route('page.product-list', $slug) }}" @if(request()->routeIs('page.product-list')) aria-current="page" @endif>
        <span aria-hidden="true">▦</span><span>{{ __('Shop') }}</span>
    </a>
    <a href="{{ route('page.cart', $slug) }}" class="rc-mobile-nav__cart" aria-label="{{ __('Cart') }}">
        <span aria-hidden="true">▢</span><span class="rc-mobile-nav__badge" data-rc-cart-count aria-live="polite" aria-atomic="true">{{ $mobileCartCount }}</span><span>{{ __('Cart') }}</span>
    </a>
    @auth('customers')
        <a href="{{ route('wishlist', $slug) }}" @if(request()->routeIs('wishlist')) aria-current="page" @endif>
            <span aria-hidden="true">♡</span><span>{{ __('Wishlist') }}</span>
        </a>
        <a href="{{ route('my-account.index', $slug) }}" @if(request()->routeIs('my-account.*')) aria-current="page" @endif>
            <span aria-hidden="true">◉</span><span>{{ __('Account') }}</span>
        </a>
    @else
        <a href="{{ route('customer.login', $slug) }}" @if(request()->routeIs('customer.login')) aria-current="page" @endif>
            <span aria-hidden="true">◉</span><span>{{ __('Sign in') }}</span>
        </a>
    @endauth
</nav>
