@php
    $logoPath = $themeSettings['header_logo'] ?? '';
    $logoUrl = !empty($logoPath) ? get_file($logoPath) : asset('themes/readycart/assets/images/logo-mark.png');
    $mainCategories = collect($MainCategoryList ?? []);
    $cartCount = \App\Models\Cart::CartCount($slug ?? $store->slug);
@endphp
@if (($themeSettings['header_status'] ?? 1) == 1)
<header class="rc-header">
    <div class="rc-topbar">
        <div class="rc-container rc-topbar__inner">
            <span>{{ $themeSettings['header_announcement'] ?? __('Free delivery on qualifying orders') }}</span>
            <div class="rc-topbar__links">
                <a href="{{ route('page.product-list', $slug) }}">{{ __('Daily deals') }}</a>
                <a href="{{ route('page.product-list', $slug) }}">{{ __('Easy returns') }}</a>
                <span>{{ $store->name }}</span>
            </div>
        </div>
    </div>

    <div class="rc-container rc-header-main">
        <div class="rc-header-brand">
            <button type="button" id="mobile-menu-toggle" class="rc-icon-button rc-menu-toggle" aria-label="{{ __('Open menu') }}" aria-controls="mobile-menu">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
            <a href="{{ route('landing_page', $slug) }}" class="rc-brand" aria-label="{{ $store->name }} home">
                <img src="{{ $logoUrl }}" alt="{{ $store->name }}" width="44" height="44" fetchpriority="high">
                <span>{{ $store->name }}</span>
            </a>
        </div>

        @if (($themeSettings['header_search_status'] ?? 1) == 1)
        <form action="{{ route('page.product-list', $slug) }}" method="GET" role="search" id="desktop-search-form" class="rc-search">
            <label class="sr-only" for="desktop-search-input">{{ __('Search products') }}</label>
            <input id="desktop-search-input" type="search" name="search_product" value="{{ request('search_product') }}" placeholder="{{ __('Search products, brands and more') }}" list="rc-search-products">
            <datalist id="rc-search-products">
                @foreach (($search_products ?? []) as $searchProduct)
                    <option value="{{ $searchProduct }}"></option>
                @endforeach
            </datalist>
            <button type="submit" aria-label="{{ __('Search') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg>
            </button>
        </form>
        @endif

        <div class="rc-header-actions">
            @if (($themeSettings['header_login_status'] ?? 1) == 1)
                @auth('customers')
                    <a class="rc-icon-button" href="{{ route('my-account.index', $slug) }}" aria-label="{{ __('My account') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 20c.7-3.5 3.5-5.5 7.5-5.5s6.8 2 7.5 5.5"/></svg>
                    </a>
                @else
                    <a class="rc-icon-button" href="{{ route('customer.login', $slug) }}" aria-label="{{ __('Sign in') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 20c.7-3.5 3.5-5.5 7.5-5.5s6.8 2 7.5 5.5"/></svg>
                    </a>
                @endauth
            @endif
            @if (($themeSettings['header_wishlist_status'] ?? 1) == 1)
                @auth('customers')
                    <a class="rc-icon-button rc-count-button wishlist-header wish-header" href="{{ route('wishlist', $slug) }}" aria-label="{{ __('Wishlist') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 8.8c0 5.2-8.8 10.2-8.8 10.2S3.2 14 3.2 8.8a4.7 4.7 0 0 1 8.8-2.3 4.7 4.7 0 0 1 8.8 2.3Z"/></svg>
                        <span class="rc-count">{{ \App\Models\Wishlist::WishCount() }}</span>
                    </a>
                @endauth
            @endif
            @if (($themeSettings['header_cart_status'] ?? 1) == 1)
                <button type="button" id="cart-toggle" class="rc-icon-button rc-count-button cart-header" aria-label="{{ __('Open cart') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l-1.3 12H6.3L5 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg>
                    <span id="cart-count" class="rc-count" data-rc-cart-count aria-live="polite" aria-atomic="true">{{ $cartCount }}</span>
                </button>
            @endif
        </div>
    </div>

    <div class="rc-container rc-navigation-row">
        <details class="rc-category-menu">
            <summary>{{ __('All categories') }} <span aria-hidden="true">⌄</span></summary>
            <ul>
                @foreach ($mainCategories as $category)
                    <li><a href="{{ url($slug . '/' . $category->slug) }}">{{ $category->name }}</a></li>
                @endforeach
            </ul>
        </details>
        <nav aria-label="{{ __('Primary navigation') }}" class="rc-primary-nav">
            <a href="{{ route('landing_page', $slug) }}" @if(request()->routeIs('landing_page')) aria-current="page" @endif>{{ __('Home') }}</a>
            <a href="{{ route('page.product-list', $slug) }}" @if(request()->routeIs('page.product-list')) aria-current="page" @endif>{{ __('Products') }}</a>
            <a href="{{ route('page.cart', $slug) }}" @if(request()->routeIs('page.cart')) aria-current="page" @endif>{{ __('Cart') }}</a>
            @if (($themeSettings['header_wishlist_status'] ?? 1) == 1 && auth('customers')->check())
                <a href="{{ route('wishlist', $slug) }}">{{ __('Wishlist') }}</a>
            @endif
        </nav>
    </div>
</header>
@endif
