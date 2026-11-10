@php extract(include base_path('themes/readycommerce/views/components/readycart-data.php')); @endphp
@php
    $storeSlug = $slug ?? ($store->slug ?? request()->route('storeSlug') ?? 'stylique');
    $homeUrl = route('landing_page', $storeSlug);
    $productsUrl = route('page.product-list', ['storeSlug' => $storeSlug]);
    $checkoutUrl = $homeUrl . '?rc_checkout=1';
    $mobileCartCount = (int) (\App\Models\Cart::CartCount($storeSlug) ?? 0);
@endphp

{{-- Floating Cart Widget on Right Edge (Desktop) --}}
<button
    type="button"
    data-rc-cart-open
    aria-label="Open cart"
    class="fixed right-0 top-1/2 z-40 hidden w-[52px] -translate-y-1/2 flex-col overflow-hidden rounded-l-lg border border-r-0 border-brand/25 bg-white shadow-[0_8px_20px_-10px_rgba(28,27,34,0.3)] transition hover:-translate-x-1 md:flex"
>
    <span class="flex flex-col items-center gap-0.5 px-1 py-2 text-brand">
        <i class="fas fa-shopping-bag text-sm" aria-hidden="true"></i>
        <span class="text-[9px] font-medium leading-tight text-ink/65"><span data-rc-cart-count>{{ $mobileCartCount }}</span> items</span>
    </span>
    <span class="bg-brand py-1 text-center text-[10px] font-bold leading-tight text-brand-foreground" data-rc-cart-total>$0.00</span>
</button>

{{-- Slide-over Cart Drawer --}}
<div data-rc-cart-backdrop class="invisible fixed inset-0 z-[70] bg-ink/40 opacity-0 backdrop-blur-[2px] transition" aria-hidden="true"></div>
<aside
    data-rc-cart-drawer
    role="dialog"
    aria-label="Shopping cart"
    aria-hidden="true"
    class="fixed right-0 top-0 z-[70] flex h-full w-[min(100%,26rem)] translate-x-full flex-col bg-white shadow-2xl transition-transform duration-300"
>
    <div class="flex items-center justify-between border-b border-line px-5 py-4">
        <h2 class="text-lg font-bold">Your Cart</h2>
        <button type="button" data-rc-cart-close aria-label="Close cart" class="flex size-9 items-center justify-center rounded-full hover:bg-surface">
            <i class="fas fa-times text-base" aria-hidden="true"></i>
        </button>
    </div>
    <div data-rc-cart-body class="flex-1 space-y-4 overflow-y-auto p-5">
        <p class="py-16 text-center text-sm text-ink/55">Your cart is empty.</p>
    </div>
    <div class="space-y-3 border-t border-line p-5">
        <div class="flex items-center justify-between text-sm">
            <span class="text-ink/60">Subtotal</span>
            <span class="text-lg font-extrabold" data-rc-cart-subtotal>$0.00</span>
        </div>
        <a
            href="{{ $checkoutUrl }}"
            data-rc-cart-checkout
            class="flex h-11 w-full items-center justify-center rounded-md bg-brand font-semibold text-brand-foreground transition hover:bg-brand-dark"
        >
            Checkout
        </a>
    </div>
</aside>

{{-- Mobile Category Drawer --}}
<div data-rc-cats-backdrop class="pointer-events-none fixed inset-0 z-[70] bg-ink/40 opacity-0 transition-opacity lg:hidden" aria-hidden="true"></div>
<aside
    data-rc-cats-drawer
    role="dialog"
    aria-label="Categories"
    class="fixed left-0 top-0 z-[70] flex h-full w-[85%] max-w-sm -translate-x-full flex-col bg-white shadow-2xl transition-transform duration-300 lg:hidden"
>
    <div class="flex h-14 items-center justify-between border-b border-line px-4">
        <h2 class="text-base font-bold">All Categories</h2>
        <button type="button" data-rc-cats-close aria-label="Close categories" class="flex size-9 items-center justify-center text-ink/70">
            <i class="fas fa-times text-base" aria-hidden="true"></i>
        </button>
    </div>
    <ul class="flex-1 overflow-y-auto py-1">
        @foreach ($rcCategories as $catName)
            <li>
                <a
                    href="{{ $productsUrl }}?category={{ urlencode($catName) }}"
                    class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm font-medium text-ink/85 active:bg-brand-soft"
                >
                    <img src="{{ $rcCategoryImages[$catName] ?? asset('readycart/p-heater.png') }}" alt="" class="size-9 rounded-md bg-surface object-contain p-1" />
                    <span class="flex-1">{{ $catName }}</span>
                    <i class="fas fa-chevron-right text-xs text-ink/30" aria-hidden="true"></i>
                </a>
            </li>
        @endforeach
    </ul>
    <a href="{{ $productsUrl }}" class="m-4 flex h-11 items-center justify-center rounded-md bg-brand text-sm font-semibold text-brand-foreground">
        View All Categories
    </a>
</aside>

{{-- Mobile Bottom Navigation --}}
<nav aria-label="App navigation" class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur md:hidden">
    <ul class="flex items-end">
        <li class="flex flex-1">
            <a href="{{ $homeUrl }}" class="relative flex flex-1 flex-col items-center justify-center gap-0.5 py-2 text-[11px] font-medium {{ request()->routeIs('landing_page') ? 'text-brand' : 'text-ink/60' }}">
                <i class="fas fa-home text-base" aria-hidden="true"></i>
                Home
            </a>
        </li>
        <li class="flex flex-1">
            <button type="button" data-rc-cats-open class="relative flex flex-1 flex-col items-center justify-center gap-0.5 py-2 text-[11px] font-medium text-ink/60">
                <i class="fas fa-th-large text-base" aria-hidden="true"></i>
                Categories
            </button>
        </li>
        <li class="flex flex-1 justify-center">
            <button
                type="button"
                data-rc-cart-open
                aria-label="Open cart"
                class="relative -mt-5 flex size-14 flex-col items-center justify-center rounded-full border-4 border-white bg-brand text-brand-foreground shadow-lg"
            >
                <i class="fas fa-shopping-bag text-lg" aria-hidden="true"></i>
                <span class="absolute -right-0.5 -top-0.5 flex size-5 items-center justify-center rounded-full bg-ink text-[10px] font-bold leading-none text-white" data-rc-cart-count>{{ $mobileCartCount }}</span>
            </button>
        </li>
        <li class="flex flex-1">
            <button type="button" data-rc-wishlist-header class="relative flex flex-1 flex-col items-center justify-center gap-0.5 py-2 text-[11px] font-medium text-ink/60">
                <i class="far fa-heart text-base" aria-hidden="true"></i>
                Wishlist
            </button>
        </li>
        <li class="flex flex-1">
            <a href="{{ auth('customers')->check() ? route('customer.SetProfile', $storeSlug) : route('customer.login', $storeSlug) }}" class="relative flex flex-1 flex-col items-center justify-center gap-0.5 py-2 text-[11px] font-medium text-ink/60">
                <i class="far fa-user text-base" aria-hidden="true"></i>
                {{ auth('customers')->check() ? 'Account' : 'Login' }}
            </a>
        </li>
    </ul>
</nav>

{{-- Toast Notification --}}
<div aria-live="polite" class="pointer-events-none fixed inset-x-0 top-6 z-[80] flex justify-center">
    <div data-rc-toast class="-translate-y-3 rounded-full bg-ink px-5 py-2.5 text-sm font-medium text-white opacity-0 shadow-xl transition duration-300"></div>
</div>


