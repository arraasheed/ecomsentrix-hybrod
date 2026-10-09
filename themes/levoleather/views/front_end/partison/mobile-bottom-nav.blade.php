{{-- ==========================================================================
  Mobile bottom navigation bar (BULK-6)
  --------------------------------------------------------------------------
  - Storefront only, mobile only (hidden at >= 768px via CSS below).
  - Root element carries `data-turbo-permanent` so Turbo Drive keeps the bar
    alive across navigations instead of re-creating it. Because a permanent
    element is never re-rendered, the active state and the badge counts are
    refreshed by the vanilla JS block at the bottom of this file.
  - Routes follow the exact pattern used by partison/header.blade.php
    (route('name', $slug)); names verified in routes/customer.php.
  - Kill-switch: remove/comment the @include of this file in
    themes/stylique/views/front_end/layouts/app.blade.php — every style,
    element and script lives inside this single file, so nothing is left
    behind on the page.
  - Include position (Claude's step): inside the storefront layout body,
    AFTER front_end.partison.footer and front_end.jQueryRoute (e.g. right
    next to front_end.partison.pwa-install) so the header markup already
    exists when this file's scripts run.
  ========================================================================== --}}

<nav id="mobile-bottom-nav" data-turbo-permanent class="mnav-bar" aria-label="{{ __('Mobile navigation') }}">

    {{-- Home — routes/customer.php:66 landing_page, same link as header logo (header.blade.php:10) --}}
    <a href="{{ route('landing_page', $slug) }}" class="mnav-item @if(request()->routeIs('landing_page')) is-active @endif" data-nav="home">
        <span class="mnav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
            </svg>
        </span>
        <span class="mnav-label">{{ __('Home') }}</span>
    </a>

    {{-- Category — routes/customer.php:75 collections (categories listing page, HomeController::showCategory) --}}
    <a href="{{ route('collections', $slug) }}" class="mnav-item @if(request()->routeIs('collections') || request()->routeIs('collections.all')) is-active @endif" data-nav="category">
        <span class="mnav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
        </span>
        <span class="mnav-label">{{ __('Category') }}</span>
    </a>

    {{-- Cart — routes/customer.php:60 page.cart (cart page; the header cart icon opens a drawer instead, this is a real page) --}}
    <a href="{{ route('page.cart', $slug) }}" class="mnav-item @if(request()->routeIs('page.cart')) is-active @endif" data-nav="cart">
        <span class="mnav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
            </svg>
            <span id="mnav-cart-badge" class="mnav-badge mnav-badge--hidden">{{ \App\Models\Cart::CartCount($slug) }}</span>
        </span>
        <span class="mnav-label">{{ __('Cart') }}</span>
    </a>

    {{-- Wishlist — routes/customer.php:64 wishlist, same link as header (header.blade.php:81) --}}
    <a href="{{ route('wishlist', $slug) }}" class="mnav-item @if(request()->routeIs('wishlist')) is-active @endif" data-nav="wishlist">
        <span class="mnav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
            <span id="mnav-wishlist-badge" class="mnav-badge @if(\App\Models\Wishlist::WishCount() < 1) mnav-badge--hidden @endif">{{ \App\Models\Wishlist::WishCount() }}</span>
        </span>
        <span class="mnav-label">{{ __('Wishlist') }}</span>
    </a>

    {{-- Account — routes/customer.php:138 my-account resource (auth) / :79 customer.login (guest), same pattern as header (header.blade.php:62/70) --}}
    @auth('customers')
    <a href="{{ route('my-account.index', $slug) }}" class="mnav-item @if(request()->routeIs('my-account.*')) is-active @endif" data-nav="account">
        <span class="mnav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </span>
        <span class="mnav-label">{{ __('Account') }}</span>
    </a>
    @endauth
    @guest('customers')
    <a href="{{ route('customer.login', $slug) }}" class="mnav-item @if(request()->routeIs('customer.login')) is-active @endif" data-nav="account">
        <span class="mnav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </span>
        <span class="mnav-label">{{ __('Account') }}</span>
    </a>
    @endguest
</nav>

@push('page-style')
<style>
    /* ---- Mobile bottom navigation (BULK-6) ---- */
    #mobile-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        /* Measured from the compiled store bundle: header .sticky-header = 11,
           .overlay = 12, storefront modals (Tailwind z-50) = 50, cart drawer
           .cart-overlay/.cart-panel = 999/1000. The bar must sit under all of
           them, above nothing. */
        z-index: 10;
        display: flex;
        align-items: stretch;
        justify-content: space-around;
        background-color: #ffffff;
        border-top: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.06);
        padding-bottom: env(safe-area-inset-bottom);
        -webkit-tap-highlight-color: transparent;
    }

    #mobile-bottom-nav .mnav-item {
        flex: 1 1 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
        min-width: 44px;
        min-height: 44px;
        padding: 7px 4px;
        color: #4b5563;
        text-decoration: none;
        transition: color 0.15s ease, background-color 0.15s ease;
    }

    #mobile-bottom-nav .mnav-item:active {
        background-color: rgba(0, 0, 0, 0.05);
    }

    #mobile-bottom-nav .mnav-icon {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    #mobile-bottom-nav .mnav-icon svg {
        width: 24px;
        height: 24px;
        stroke: currentColor;
        fill: none;
    }

    #mobile-bottom-nav .mnav-label {
        font-size: 10px;
        line-height: 1;
        font-weight: 500;
    }

    #mobile-bottom-nav .mnav-item.is-active {
        color: var(--primary-color, #f97316);
    }

    #mobile-bottom-nav .mnav-item.is-active .mnav-label {
        font-weight: 700;
    }

    #mobile-bottom-nav .mnav-badge {
        position: absolute;
        top: -6px;
        inset-inline-end: -10px;
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        border-radius: 9999px;
        background-color: var(--primary-color, #f97316);
        color: #ffffff;
        font-size: 10px;
        line-height: 16px;
        font-weight: 600;
        text-align: center;
    }

    #mobile-bottom-nav .mnav-badge--hidden {
        display: none;
    }

    /* Reserve room for the fixed bar so footer content stays reachable.
       env() term keeps the reservation correct on notched iPhones. */
    @media (max-width: 767.98px) {
        body {
            padding-bottom: calc(60px + env(safe-area-inset-bottom));
        }
    }

    @media (min-width: 768px) {
        #mobile-bottom-nav {
            display: none;
        }
    }
</style>
@endpush

@push('page-script')
<script>
    /* ---- Mobile bottom navigation (BULK-6) — vanilla JS, no jQuery ----
       The nav root is data-turbo-permanent, so on every navigation the
       active item and badge counts must be recomputed from the fresh page
       (header badges / URL) rather than trusted from the first render. */
    (function () {
        'use strict';

        var NAV_ID = 'mobile-bottom-nav';

        function keyFromPath() {
            var path = window.location.pathname;
            if (/\/home\/?$/.test(path)) return 'home';
            if (/\/collections(\/|$)/.test(path)) return 'category';
            if (/\/cart\/?$/.test(path)) return 'cart';
            if (/\/wishlist\/?$/.test(path)) return 'wishlist';
            if (/\/(my-account|login)(\/|$)/.test(path)) return 'account';
            return '';
        }

        function setActive() {
            var nav = document.getElementById(NAV_ID);
            if (!nav) return;
            var key = keyFromPath();
            var items = nav.querySelectorAll('.mnav-item');
            for (var i = 0; i < items.length; i++) {
                var match = key !== '' && items[i].getAttribute('data-nav') === key;
                items[i].classList.toggle('is-active', match);
            }
        }

        function setBadge(badge, value) {
            if (!badge) return;
            var count = parseInt(value, 10);
            if (isNaN(count) || count <= 0) {
                badge.textContent = '0';
                badge.classList.add('mnav-badge--hidden');
            } else {
                badge.textContent = String(count);
                badge.classList.remove('mnav-badge--hidden');
            }
        }

        function syncBadges() {
            var headerCart = document.getElementById('cart-count');
            setBadge(document.getElementById('mnav-cart-badge'), headerCart ? headerCart.textContent : '');
            var headerWish = document.querySelector('.wishlist-header .cart-badge');
            setBadge(document.getElementById('mnav-wishlist-badge'), headerWish ? headerWish.textContent : '');
        }

        var cartObserver = null;

        function observeHeaderCart() {
            var headerCart = document.getElementById('cart-count');
            if (!headerCart || !window.MutationObserver) return;
            if (cartObserver) cartObserver.disconnect();
            cartObserver = new MutationObserver(function () {
                syncBadges();
            });
            cartObserver.observe(headerCart, { childList: true, characterData: true, subtree: true });
        }

        function init() {
            setActive();
            syncBadges();
            observeHeaderCart();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }

        /* turbo:load also fires on the initial page load; with
           TURBO_ENABLED=false this branch simply never runs and the
           DOMContentLoaded/inline init() above does the whole job. */
        if (window.onTurboLoad) {
            window.onTurboLoad(init);
        } else {
            document.addEventListener('turbo:load', init);
        }
    })();
</script>
@endpush
