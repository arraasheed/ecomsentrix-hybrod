@php extract(include base_path('themes/readycommerce/views/components/readycart-data.php')); @endphp
@php
    $storeSlug = $slug ?? ($store->slug ?? request()->route('storeSlug') ?? 'stylique');
    $homeUrl = route('landing_page', $storeSlug);
    $productsUrl = route('page.product-list', ['storeSlug' => $storeSlug]);
    $contactUrl = Route::has('page.contact_us') ? route('page.contact_us', ['storeSlug' => $storeSlug]) : $productsUrl;
    $blogUrl = Route::has('page.blog') ? route('page.blog', ['storeSlug' => $storeSlug]) : $productsUrl;
    $wishlistCount = (int) ($wishlist_count ?? 0);
    $cartCount = (int) ($count ?? 0);
    $isHome = request()->routeIs('landing_page') && !request()->has('rc_product') && !request()->has('rc_checkout');
    $isProducts = request()->routeIs('page.product-list');

    $navItems = [
        ['label' => 'Home', 'url' => $homeUrl, 'active' => $isHome],
        ['label' => 'Products', 'url' => $productsUrl, 'active' => $isProducts],
        ['label' => 'Shops', 'url' => $productsUrl, 'active' => false],
        ['label' => 'Brands', 'url' => $productsUrl . '#brands', 'active' => false],
        ['label' => 'Contact', 'url' => $contactUrl, 'active' => request()->routeIs('page.contact_us')],
        ['label' => 'Blogs', 'url' => $blogUrl, 'active' => request()->routeIs('page.blog')],
    ];
@endphp

<header class="sticky top-0 z-30 border-b border-line bg-white md:static md:border-b-0">
    {{-- Top announcement bar --}}
    <div class="hidden border-b border-line text-xs text-ink/70 md:block">
        <div class="mx-auto flex h-10 max-w-[1400px] items-center justify-between px-3 sm:px-4 lg:px-6">
            <span class="flex items-center gap-2">
                <svg class="size-3.5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                Free Delivery on orders over $999.00
            </span>
            <ul class="hidden items-center gap-5 lg:flex">
                <li class="flex items-center gap-1.5">
                    <svg class="size-3.5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg>
                    Daily Deals
                </li>
                <li class="flex items-center gap-1.5">
                    <svg class="size-3.5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                    Easy Returns
                </li>
                <li class="flex items-center gap-1.5">
                    <svg class="size-3.5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                    Help Center
                </li>
            </ul>
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1">
                    English
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </span>
                <span class="flex items-center gap-1">
                    $ USD
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </span>
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
            </div>
        </div>
    </div>

    {{-- Main header row --}}
    <div class="mx-auto flex max-w-[1400px] flex-wrap items-center justify-between gap-x-4 gap-y-2.5 px-3 py-2.5 sm:px-4 md:grid md:grid-cols-[1fr_minmax(0,42rem)_1fr] md:py-4 lg:px-6">
        <div class="flex items-center gap-1 md:justify-self-start">
            <button type="button" id="mobile-menu-toggle" data-rc-cats-open aria-label="Open categories" class="-ml-1.5 flex size-9 items-center justify-center text-ink/80 lg:hidden">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
            </button>
            <a href="{{ $homeUrl }}" class="flex items-center gap-2" aria-label="Ready eCommerce home">
                <img src="{{ asset('readycart/logo-mark.png') }}" alt="" width="40" height="40" class="brand-logo size-10 shrink-0 object-contain" />
                <span class="text-[15px] font-extrabold leading-[1.05] tracking-tight text-ink">
                    Ready<br />eCommerce
                </span>
            </a>
        </div>

        <form role="search" action="{{ $productsUrl }}" method="get" class="order-last flex h-10 w-full overflow-hidden rounded-md border border-line bg-white focus-within:border-brand md:order-none md:h-11">
            <input
                id="search-product-input"
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search for products, brands and more..."
                aria-label="Search products"
                class="min-w-0 flex-1 bg-transparent px-3.5 text-sm outline-none placeholder:text-ink/40"
            />
            <label class="sr-only" for="search-category">Category</label>
            <select id="search-category" name="category" class="hidden w-40 border-l border-line bg-transparent px-3 text-xs outline-none sm:block">
                <option value="">All Categories</option>
                @foreach ($rcCategories as $catName)
                    <option value="{{ $catName }}" @selected(request('category') === $catName)>{{ $catName }}</option>
                @endforeach
            </select>
            <button type="submit" aria-label="Search" class="flex w-12 items-center justify-center bg-brand text-white transition hover:bg-brand-dark">
                <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </button>
        </form>

        <div class="flex items-center gap-1.5 md:justify-self-end">
            <button type="button" data-rc-wishlist-header aria-label="Wishlist" class="relative flex size-10 items-center justify-center rounded-full border border-line bg-white text-ink/80 transition-colors duration-200 hover:border-brand/25 hover:bg-brand-soft hover:text-brand focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/30 focus-visible:ring-offset-2">
                <svg class="size-[22px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                <span class="wishlist_count absolute right-0 top-0 flex size-[18px] items-center justify-center rounded-full bg-brand text-[10px] font-bold leading-none text-brand-foreground" data-rc-wishlist-count>{{ $wishlistCount }}</span>
            </button>

            <button type="button" id="cart-button" data-rc-cart-open aria-label="Open cart" class="cart-header relative flex size-10 items-center justify-center rounded-full border border-line bg-white text-ink/80 transition-colors duration-200 hover:border-brand/25 hover:bg-brand-soft hover:text-brand focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/30 focus-visible:ring-offset-2">
                <svg class="size-[22px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span id="cart_Badge" class="count absolute right-0 top-0 flex size-[18px] items-center justify-center rounded-full bg-brand text-[10px] font-bold leading-none text-brand-foreground" data-rc-cart-count>{{ $cartCount }}</span>
            </button>

            <a href="{{ auth('customers')->check() ? route('customer.SetProfile', $storeSlug) : route('customer.login', $storeSlug) }}" aria-label="Account" class="hidden size-10 items-center justify-center rounded-full border border-line bg-white text-ink/80 transition-colors duration-200 hover:border-brand/25 hover:bg-brand-soft hover:text-brand focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/30 focus-visible:ring-offset-2 md:flex">
                <svg class="size-[22px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </a>
        </div>
    </div>

    {{-- Navigation bar --}}
    <div class="mx-auto hidden max-w-[1400px] items-center gap-6 px-3 pb-3 sm:px-4 lg:flex lg:px-6">
        <div class="group relative w-[250px] shrink-0">
            <button
                type="button"
                aria-haspopup="{{ !$isHome ? 'menu' : 'false' }}"
                class="flex h-11 w-full items-center gap-2.5 rounded-md bg-brand px-4 text-sm font-semibold text-brand-foreground {{ $isHome ? 'cursor-default' : '' }}"
            >
                <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                All Categories
                <svg class="ml-auto size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            @if (!$isHome)
                <ul class="invisible absolute left-0 top-full z-40 mt-1 w-full rounded-md border border-line bg-white py-1 opacity-0 shadow-xl transition group-focus-within:visible group-focus-within:opacity-100 group-hover:visible group-hover:opacity-100">
                    @foreach ($rcCategories as $catName)
                        <li>
                            <a href="{{ $productsUrl }}?category={{ urlencode($catName) }}" class="flex items-center justify-between px-4 py-2 text-sm hover:bg-brand-soft hover:text-brand">
                                {{ $catName }}
                                <svg class="size-4 text-ink/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <nav aria-label="Primary" class="flex items-center gap-1">
            @foreach ($navItems as $item)
                <a
                    href="{{ $item['url'] }}"
                    @if ($item['active']) aria-current="page" @endif
                    class="relative px-3.5 py-2 text-sm transition hover:text-brand {{ $item['active'] ? 'text-brand font-medium after:absolute after:inset-x-3.5 after:-bottom-0.5 after:h-0.5 after:bg-brand' : 'text-ink/80' }}"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</header>


