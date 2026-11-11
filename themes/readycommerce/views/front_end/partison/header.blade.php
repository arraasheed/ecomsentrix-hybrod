@php extract(include base_path('themes/readycommerce/views/components/readycart-data.php')); @endphp
@php
    $storeSlug = $slug ?? ($store->slug ?? request()->route('storeSlug') ?? 'stylique');
    $storeObj = isset($store) && is_object($store) ? $store : \App\Models\Store::where('slug', $storeSlug)->first();
    $storeId = $storeObj ? (int) $storeObj->id : 2;
    $themeSettings = $themeSettings ?? (function_exists('getThemeSetting') ? getThemeSetting($storeId, $storeObj->theme_id ?? 'readycommerce') : []);

    $homeUrl = route('landing_page', $storeSlug);
    $productsUrl = route('page.product-list', ['storeSlug' => $storeSlug]);
    $contactUrl = Route::has('page.contact_us') ? route('page.contact_us', ['storeSlug' => $storeSlug]) : $productsUrl;
    $trackOrderUrl = Route::has('track.order') ? route('track.order', ['storeSlug' => $storeSlug]) : $homeUrl . '#track';
    $wishlistCount = (int) ($wishlist_count ?? 0);
    $cartCount = (int) ($count ?? 0);
    $isHome = request()->routeIs('landing_page') && !request()->has('rc_product') && !request()->has('rc_checkout');
    $isProducts = request()->routeIs('page.product-list');

    // Dynamic Header Settings from Theme Customize
    $showTopBar = !isset($themeSettings['header_top_bar_status']) || $themeSettings['header_top_bar_status'] == 1;
    $topBarText = !empty($themeSettings['header_top_bar_title']) ? $themeSettings['header_top_bar_title'] : 'Free Delivery on orders over ৳999.00';
    $hotline = !empty($themeSettings['header_contact_phone']) ? $themeSettings['header_contact_phone'] : ($storeObj->phone ?? '+880123456789');
    $whatsapp = !empty($themeSettings['header_contact_whatsapp']) ? $themeSettings['header_contact_whatsapp'] : ($storeObj->whatsapp ?? '+880123456789');

    // Dynamic Store Logo
    if (!empty($themeSettings['header_logo'])) {
        $headerLogo = get_file($themeSettings['header_logo']);
    } elseif (!empty($storeObj->logo)) {
        $headerLogo = get_file($storeObj->logo);
    } else {
        $headerLogo = asset('readycart/logo-mark.png');
    }
    $storeName = $storeObj->name ?? 'Ready eCommerce';
    $currencySign = $storeObj->currency ?? '৳';

    $navItems = [
        ['label' => 'Home', 'url' => $homeUrl, 'active' => $isHome],
        ['label' => 'Products', 'url' => $productsUrl, 'active' => $isProducts],
        ['label' => 'Track Order', 'url' => $trackOrderUrl, 'active' => request()->routeIs('track.order')],
        ['label' => 'Contact', 'url' => $contactUrl, 'active' => request()->routeIs('page.contact_us')],
    ];
@endphp

<header class="sticky top-0 z-30 border-b border-line bg-white md:static md:border-b-0">
    {{-- Top announcement bar --}}
    @if ($showTopBar)
    <div class="hidden border-b border-line text-xs text-ink/70 md:block bg-slate-50/70">
        <div class="mx-auto flex h-10 max-w-[1400px] items-center justify-between px-3 sm:px-4 lg:px-6">
            <span class="flex items-center gap-2 font-medium text-ink/80">
                <svg class="size-3.5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                {{ $topBarText }}
            </span>
            <ul class="hidden items-center gap-5 lg:flex">
                <li>
                    <a href="tel:{{ $hotline }}" class="flex items-center gap-1.5 transition hover:text-brand">
                        <svg class="size-3.5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Hotline: {{ $hotline }}
                    </a>
                </li>
                <li>
                    <a href="{{ $trackOrderUrl }}" class="flex items-center gap-1.5 font-semibold text-brand transition hover:underline">
                        <svg class="size-3.5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="16" height="13" x="2" y="6" rx="2"/><path d="M16 8h4l3 3v6h-7V8z"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="18" r="2"/></svg>
                        Track Order (অর্ডার ট্র্যাক)
                    </a>
                </li>
            </ul>
            <div class="flex items-center gap-4">
                {{-- Language Selector: English / বাংলা --}}
                <div class="flex items-center gap-1.5 text-xs text-ink/75 font-medium">
                    <span class="cursor-pointer text-brand font-bold">EN</span>
                    <span class="text-ink/30">|</span>
                    <span class="cursor-pointer hover:text-brand">বাং</span>
                </div>
                {{-- Currency --}}
                <span class="flex items-center gap-1 font-semibold text-ink/80">
                    {{ $currencySign }} BDT
                </span>
            </div>
        </div>
    </div>
    @endif

    {{-- Main header row --}}
    <div class="mx-auto flex max-w-[1400px] flex-wrap items-center justify-between gap-x-4 gap-y-2.5 px-3 py-2.5 sm:px-4 md:grid md:grid-cols-[1fr_minmax(0,42rem)_1fr] md:py-4 lg:px-6">
        <div class="flex items-center gap-1 md:justify-self-start">
            <button type="button" id="mobile-menu-toggle" data-rc-cats-open aria-label="Open categories" class="-ml-1.5 flex size-9 items-center justify-center text-ink/80 lg:hidden">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
            </button>
            <a href="{{ $homeUrl }}" class="flex items-center gap-2" aria-label="{{ $storeName }}">
                <img src="{{ $headerLogo }}" alt="{{ $storeName }}" width="40" height="40" class="brand-logo size-10 shrink-0 object-contain rounded-md" />
                <span class="text-[15px] font-extrabold leading-[1.05] tracking-tight text-ink">
                    {{ $storeName }}
                </span>
            </a>
        </div>

        <form role="search" action="{{ $productsUrl }}" method="get" class="order-last flex h-10 w-full overflow-hidden rounded-md border border-line bg-white focus-within:border-brand md:order-none md:h-11">
            <input
                id="search-product-input"
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search for products, categories..."
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

        <div class="flex items-center gap-2 md:justify-self-end">
            {{-- WhatsApp 1-Click Order Link --}}
            @if (!empty($whatsapp))
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}?text={{ urlencode('Hello, I want to order from ' . $storeName) }}" target="_blank" rel="noopener" aria-label="WhatsApp" class="flex size-10 items-center justify-center rounded-full border border-emerald-200 bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100" title="Order on WhatsApp">
                <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
            </a>
            @endif

            <button type="button" data-rc-wishlist-header aria-label="Wishlist" class="relative flex size-10 items-center justify-center rounded-full border border-line bg-white text-ink/80 transition-colors duration-200 hover:border-brand/25 hover:bg-brand-soft hover:text-brand focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/30 focus-visible:ring-offset-2">
                <svg class="size-[22px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                <span class="wishlist_count absolute right-0 top-0 flex size-[18px] items-center justify-center rounded-full bg-brand text-[10px] font-bold leading-none text-brand-foreground" data-rc-wishlist-count>{{ $wishlistCount }}</span>
            </button>

            <button type="button" id="cart-button" data-rc-cart-open aria-label="Open cart" class="cart-header relative flex size-10 items-center justify-center rounded-full border border-line bg-white text-ink/80 transition-colors duration-200 hover:border-brand/25 hover:bg-brand-soft hover:text-brand focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/30 focus-visible:ring-offset-2">
                <svg class="size-[22px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span id="cart_Badge" class="count absolute right-0 top-0 flex size-[18px] items-center justify-center rounded-full bg-brand text-[10px] font-bold leading-none text-brand-foreground" data-rc-cart-count>{{ $cartCount }}</span>
            </button>

            <a href="{{ auth('customers')->check() ? route('customer.SetProfile', $storeSlug) : route('customer.login', $storeSlug) }}" aria-label="Account" class="hidden size-10 items-center justify-center rounded-full border border-line bg-white text-ink/80 transition-colors duration-200 hover:border-brand/25 hover:bg-brand-soft hover:text-brand focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/30 focus-visible:ring-offset-2 md:flex" title="Customer Profile / Login">
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
