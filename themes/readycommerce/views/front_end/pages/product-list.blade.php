@extends('front_end.layouts.app')

@section('page-title')
    {{ __('All Products') }}
@endsection

@section('content')
@php extract(include base_path('themes/readycommerce/views/components/readycart-data.php')); @endphp
@php
    $storeSlug = $slug ?? ($store->slug ?? request()->route('storeSlug') ?? 'stylique');
    $homeUrl = route('landing_page', $storeSlug);
    $productsUrl = route('page.product-list', ['storeSlug' => $storeSlug]);

    $searchQuery = strtolower(trim((string) request('search', '')));
    $selectedCat = trim((string) request('category', ''));
    $selectedBrand = trim((string) request('brand', ''));

    $visibleProducts = array_values(array_filter($rcProducts, function ($p) use ($searchQuery, $selectedCat, $selectedBrand) {
        if ($searchQuery !== '' && !str_contains(strtolower($p['name'] . ' ' . $p['brand'] . ' ' . $p['category']), $searchQuery)) {
            return false;
        }
        if ($selectedCat !== '' && strcasecmp($p['category'], $selectedCat) !== 0) {
            return false;
        }
        if ($selectedBrand !== '' && strcasecmp($p['brand'], $selectedBrand) !== 0) {
            return false;
        }
        return true;
    }));
@endphp

<main class="mx-auto max-w-[1400px] px-3 pb-10 sm:px-4 lg:px-6" data-rc-products-page>
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-5 flex items-center gap-1.5 text-xs text-ink/55">
        <a href="{{ $homeUrl }}" aria-label="Home" class="hover:text-brand">
            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        </a>
        <span class="flex items-center gap-1.5">
            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            <span class="font-semibold text-ink">All Products</span>
        </span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-[280px_minmax(0,1fr)]">
        <div data-rc-filter-backdrop class="pointer-events-none fixed inset-0 z-[70] bg-ink/40 opacity-0 transition-opacity lg:hidden" aria-hidden="true"></div>

        <aside
            data-rc-filter-panel
            aria-label="Filters"
            class="fixed left-0 top-0 z-[70] h-full w-[85%] max-w-sm -translate-x-full overflow-y-auto bg-white p-5 shadow-2xl transition-transform duration-300 lg:sticky lg:top-4 lg:z-auto lg:h-auto lg:w-auto lg:max-w-none lg:translate-x-0 lg:self-start lg:rounded-lg lg:border lg:border-line lg:shadow-none"
        >
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-extrabold">Filter By</h2>
                <div class="flex items-center gap-3">
                    <button type="button" data-rc-filter-clear class="text-xs font-semibold text-brand">Clear All</button>
                    <button type="button" data-rc-filter-close aria-label="Close filters" class="flex size-8 items-center justify-center lg:hidden">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
            </div>

            <div class="border-b border-line py-4">
                <p class="mb-3 text-sm font-bold">Product Price</p>
                <div class="flex items-center gap-2">
                    <input data-rc-price-min aria-label="Minimum price" inputmode="numeric" value="0" class="h-10 w-full min-w-0 rounded-lg border border-line px-3 text-sm outline-none focus:border-brand" />
                    <span class="text-xs text-ink/45">To</span>
                    <input data-rc-price-max aria-label="Maximum price" inputmode="numeric" value="150" class="h-10 w-full min-w-0 rounded-lg border border-line px-3 text-sm outline-none focus:border-brand" />
                    <button type="button" data-rc-price-apply class="h-10 rounded-md bg-brand px-4 text-sm font-semibold text-brand-foreground hover:bg-brand-dark">Apply</button>
                </div>
            </div>

            <div class="border-b border-line py-4">
                <label for="rc-sort" class="mb-3 block text-sm font-bold">Sort By</label>
                <select id="rc-sort" data-rc-sort class="h-10 w-full rounded-lg border border-line bg-white px-3 text-sm outline-none focus:border-brand">
                    <option value="default">Default Sorting</option>
                    <option value="low">Price: Low to High</option>
                    <option value="high">Price: High to Low</option>
                    <option value="popular">Most Popular</option>
                </select>
            </div>

            <div class="flex items-center justify-between border-b border-line py-4">
                <span class="text-sm font-semibold">Digital Products</span>
                <button type="button" role="switch" aria-checked="false" data-rc-switch class="relative h-6 w-11 rounded-full bg-ink/15 transition">
                    <span class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow transition"></span>
                </button>
            </div>

            <div class="flex items-center justify-between border-b border-line py-4">
                <span class="text-sm font-semibold">Preorder Products</span>
                <button type="button" role="switch" aria-checked="false" data-rc-switch class="relative h-6 w-11 rounded-full bg-ink/15 transition">
                    <span class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow transition"></span>
                </button>
            </div>

            <fieldset class="border-b border-line py-4">
                <legend class="mb-3 text-sm font-bold">Categories</legend>
                <ul class="space-y-2.5">
                    @foreach (array_slice($rcCategories, 0, 5) as $catName)
                        <li>
                            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-ink/75">
                                <input type="checkbox" value="{{ $catName }}" data-rc-filter-cat @checked(strcasecmp($selectedCat, $catName) === 0) class="size-4 rounded accent-brand" />
                                {{ $catName }}
                            </label>
                        </li>
                    @endforeach
                </ul>
            </fieldset>

            <fieldset class="border-b border-line py-4">
                <legend class="mb-3 text-sm font-bold">Brands</legend>
                <ul class="space-y-2.5">
                    @foreach ($rcBrands as $brandName)
                        <li>
                            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-ink/75">
                                <input type="checkbox" value="{{ $brandName }}" data-rc-filter-brand @checked(strcasecmp($selectedBrand, $brandName) === 0) class="size-4 rounded accent-brand" />
                                {{ $brandName }}
                            </label>
                        </li>
                    @endforeach
                </ul>
            </fieldset>
        </aside>

        <section aria-label="Product list">
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight">All Products</h1>
                    <p class="text-sm text-ink/55"><span data-rc-visible-count>{{ count($visibleProducts) }}</span> products</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" data-rc-filter-open class="flex h-10 items-center gap-2 rounded-lg border border-line px-3 text-sm font-semibold lg:hidden">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/></svg>
                        Filters
                    </button>
                    <div class="flex rounded-md bg-surface p-1">
                        <button type="button" data-rc-view-mode="grid" aria-label="grid view" aria-pressed="true" class="flex size-9 items-center justify-center rounded-lg bg-white text-brand shadow-sm">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                        </button>
                        <button type="button" data-rc-view-mode="list" aria-label="list view" aria-pressed="false" class="flex size-9 items-center justify-center rounded-lg text-ink/50">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div data-rc-products-grid class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($visibleProducts as $prod)
                    <x-product-card :store="$store" :product="$prod" />
                @endforeach
            </div>

            <div class="mt-8 flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-ink/65">
                    Show
                    <select class="h-9 rounded-lg border border-line bg-white px-2 text-sm">
                        <option>20</option>
                        <option>40</option>
                    </select>
                </label>
                <nav aria-label="Pagination" class="flex gap-2">
                    @foreach (['1', '2', '3', '>'] as $idx => $pLabel)
                        <button
                            type="button"
                            @if ($idx === 0) aria-current="page" @endif
                            class="size-10 rounded-lg border border-line text-sm font-semibold transition hover:border-brand hover:text-brand {{ $idx === 0 ? 'border-brand bg-brand text-brand-foreground hover:text-brand-foreground' : '' }}"
                        >
                            {{ $pLabel }}
                        </button>
                    @endforeach
                </nav>
            </div>
        </section>
    </div>
</main>
@endsection


