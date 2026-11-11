@php extract(include base_path('themes/readycommerce/views/components/readycart-data.php')); @endphp
@php
    $storeSlug = $slug ?? ($store->slug ?? request()->route('storeSlug') ?? 'stylique');
    $homeUrl = route('landing_page', $storeSlug);
    $productsUrl = route('page.product-list', ['storeSlug' => $storeSlug]);
    $checkoutUrl = route('checkout', $storeSlug);

    $targetId = (int) ($rcProductId ?? request('rc_product', 0));
    $reqSlug = (string) (request()->route('product_slug') ?? request('product_slug', ''));
    if ($reqSlug !== '') {
        $activeProd = collect($rcProducts)->firstWhere('slug', $reqSlug);
    }
    if (empty($activeProd) && $targetId > 0) {
        $activeProd = collect($rcProducts)->firstWhere('id', $targetId);
    }
    $activeProd = $activeProd ?? $rcProducts[0];

    $isInStock = true;
    if (isset($product) && is_object($product) && !isset($rcProductId)) {
        $rawName = (string) ($product->name ?? 'Product');
        $rawImg = (string) ($product->cover_image_path ?? '');
        $sale = (float) ($product->sale_price ?: $product->final_price ?: $product->price ?: 49.00);
        $orig = (float) ($product->price ?: $sale);
        if ($orig < $sale) $orig = $sale;
        $imgUrl = (!empty($rawImg) && !str_contains($rawImg, 'themes/stylique/assets/images/service') && !str_starts_with($rawName, 'Test '))
            ? get_file($rawImg)
            : ($activeProd['image'] ?? asset('readycart/p-heater.png'));
        $isInStock = !($product->track_stock == 1 && $product->product_stock <= 0);
        $activeProd = [
            'id' => (int) ($product->id ?? 1),
            'name' => $rawName,
            'brand' => (string) (data_get($product, 'brand.name') ?: 'ReadyCart'),
            'category' => (string) (data_get($product, 'ProductData.name') ?: 'Electronics'),
            'price' => $sale,
            'oldPrice' => $orig,
            'image' => $imgUrl,
            'rating' => (float) ($product->average_rating ?: 4.8),
            'reviews' => (int) ($product->review_count ?: 18),
            'sold' => 32,
            'is_demo' => false,
        ];
    }

    if (isset($products) && is_iterable($products) && count($products) > 0) {
        $related = collect($products)->where('id', '!=', $activeProd['id'])->take(4)->values()->all();
    } else {
        $others = array_values(array_filter($rcProducts, fn ($p) => $p['id'] !== $activeProd['id']));
        $sameCat = array_values(array_filter($others, fn ($p) => $p['category'] === $activeProd['category']));
        $diffCat = array_values(array_filter($others, fn ($p) => $p['category'] !== $activeProd['category']));
        $related = array_slice(array_merge($sameCat, $diffCat), 0, 4);
    }

    $colors = [
        ['name' => 'Black', 'hex' => '#111111'],
        ['name' => 'Ivory', 'hex' => '#f1ece3'],
        ['name' => 'Crimson', 'hex' => '#a82a2a'],
    ];
    $sizes = ['S', 'M', 'L', 'XXL'];
    $views = ['scale-100', 'scale-150 origin-top-right', 'scale-150 origin-bottom-left'];
    $details = [
        ['title' => 'Premium Quality Material', 'text' => 'Manufactured using durable, high-quality materials for long-lasting performance and reliability.'],
        ['title' => 'User-Friendly Design', 'text' => 'Designed for comfortable handling and easy operation, making it suitable for everyday use.'],
        ['title' => 'Durable Construction', 'text' => 'Built to withstand regular use while maintaining its quality and functionality over time.'],
        ['title' => 'Easy to Clean and Maintain', 'text' => 'A simple design allows for quick cleaning and hassle-free maintenance.'],
    ];
    $faqs = [
        ['q' => 'Is this product easy to use?', 'a' => 'Yes. It is designed with user convenience in mind and can be used easily without any special skills.'],
        ['q' => 'Is the product durable?', 'a' => 'Yes. It is made from quality materials that provide reliable performance under normal use.'],
        ['q' => 'How should I maintain this product?', 'a' => 'Clean it regularly and store it in a dry place to extend its lifespan.'],
    ];
@endphp

<main class="mx-auto max-w-[1400px] px-3 pb-10 sm:px-4 lg:px-6" data-rc-detail-root data-product-id="{{ $activeProd['id'] }}" data-product-name="{{ $activeProd['name'] }}" data-product-brand="{{ $activeProd['brand'] }}" data-product-price="{{ number_format($activeProd['price'], 2, '.', '') }}" data-product-image="{{ $activeProd['image'] }}">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-5 flex items-center gap-1.5 text-xs text-ink/55">
        <a href="{{ $homeUrl }}" aria-label="Home" class="hover:text-brand">
            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        </a>
        <span class="flex items-center gap-1.5">
            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            <a href="{{ $productsUrl }}" class="hover:text-brand">Products</a>
        </span>
        <span class="flex items-center gap-1.5">
            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            <span class="font-semibold text-ink">{{ $activeProd['name'] }}</span>
        </span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,540px)_minmax(0,1fr)] lg:gap-12">
        {{-- Left Gallery --}}
        <div>
            <div class="relative aspect-square overflow-hidden rounded-lg border border-line bg-surface">
                <img
                    data-rc-detail-main-img
                    src="{{ $activeProd['image'] }}"
                    alt="{{ $activeProd['name'] }}"
                    class="h-full w-full object-contain p-8 mix-blend-multiply transition duration-500 scale-100"
                />
            </div>
            <div class="mt-3 flex gap-3">
                @foreach ($views as $i => $transform)
                    <button
                        type="button"
                        data-rc-detail-thumb="{{ $transform }}"
                        aria-label="View {{ $i + 1 }}"
                        aria-pressed="{{ $i === 0 ? 'true' : 'false' }}"
                        class="relative size-20 overflow-hidden rounded-md border-2 bg-surface {{ $i === 0 ? 'border-brand' : 'border-transparent' }}"
                    >
                        <img src="{{ $activeProd['image'] }}" alt="" class="h-full w-full object-contain p-1.5 mix-blend-multiply {{ $transform }}" />
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Right Info --}}
        <div>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-brand">{{ $activeProd['brand'] }}</p>
                    <h1 class="mt-1 text-3xl font-extrabold leading-tight tracking-tight text-balance">{{ $activeProd['name'] }}</h1>
                </div>
                <div class="flex gap-2">
                    <button
                        type="button"
                        data-rc-wish-toggle="{{ $activeProd['id'] }}"
                        aria-label="Toggle wishlist"
                        class="wishbtn-globaly flex size-10 items-center justify-center rounded-md border border-line hover:border-brand hover:text-brand"
                    >
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                    </button>
                    <button type="button" aria-label="Share" class="flex size-10 items-center justify-center rounded-md border border-line hover:border-brand hover:text-brand">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/></svg>
                    </button>
                </div>
            </div>
            <p class="mt-2 text-sm text-ink/60">Crafted for everyday use with reliable quality and a clean, modern finish.</p>

            <div class="mt-3 flex items-center gap-3 text-sm">
                <span class="flex" aria-hidden="true">
                    @for ($i = 0; $i < 5; $i++)
                        <svg class="size-4 {{ $i < round($activeProd['rating']) ? 'fill-amber-400 text-amber-400' : 'text-ink/20' }}" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    @endfor
                </span>
                <span class="font-bold">{{ $activeProd['rating'] }}</span>
                <span class="text-ink/55">({{ $activeProd['reviews'] }} reviews)</span>
                <span class="text-ink/20">|</span>
                <span class="text-ink/55">{{ $activeProd['sold'] }}+ sold</span>
            </div>

            <div class="mt-5 flex items-baseline gap-3 border-b border-line pb-5">
                <span class="text-4xl font-extrabold">৳{{ number_format($activeProd['price'], 2) }}</span>
                @if ($activeProd['oldPrice'] > $activeProd['price'])
                    <span class="text-lg text-ink/40 line-through">৳{{ number_format($activeProd['oldPrice'], 2) }}</span>
                @endif
            </div>

            <div class="mt-5 space-y-5">
                <div class="flex items-center gap-5">
                    <span class="w-20 text-sm text-ink/60">Color</span>
                    <div class="flex items-center gap-3" role="radiogroup" aria-label="Color">
                        @foreach ($colors as $i => $c)
                            <button
                                type="button"
                                data-rc-color-btn="{{ $c['name'] }}"
                                aria-checked="{{ $i === 0 ? 'true' : 'false' }}"
                                aria-label="{{ $c['name'] }}"
                                class="size-8 rounded-full border border-line ring-offset-2 transition {{ $i === 0 ? 'ring-2 ring-brand' : '' }}"
                                style="background-color: {{ $c['hex'] }}"
                            ></button>
                        @endforeach
                        <span class="text-sm font-semibold" data-rc-color-label>{{ $colors[0]['name'] }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-5">
                    <span class="w-20 text-sm text-ink/60">Size</span>
                    <div class="flex gap-2" role="radiogroup" aria-label="Size">
                        @foreach ($sizes as $i => $s)
                            <button
                                type="button"
                                data-rc-size-btn="{{ $s }}"
                                aria-checked="{{ $i === 0 ? 'true' : 'false' }}"
                                class="h-10 min-w-12 rounded-lg border px-3 text-sm font-semibold transition {{ $i === 0 ? 'border-brand bg-brand-soft text-brand' : 'border-line hover:border-brand' }}"
                            >
                                {{ $s }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-5">
                    <span class="w-20 text-sm text-ink/60">Quantity</span>
                    <div class="flex items-center rounded-md border border-line">
                        <button type="button" data-rc-detail-qty="-1" aria-label="Decrease quantity" class="flex size-10 items-center justify-center hover:text-brand">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
                        </button>
                        <span class="w-10 text-center font-bold" data-rc-detail-qty-val>1</span>
                        <button type="button" data-rc-detail-qty="1" aria-label="Increase quantity" class="flex size-10 items-center justify-center hover:text-brand">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        </button>
                    </div>
                    @if ($isInStock)
                        <span class="text-sm font-semibold text-emerald-600">স্টকে আছে (In Stock)</span>
                    @else
                        <span class="text-sm font-semibold text-rose-600">স্টক আউট (Out of Stock)</span>
                    @endif
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <button
                    type="button"
                    data-rc-add-cart="{{ $activeProd['id'] }}"
                    class="h-12 min-w-44 flex-1 rounded-md bg-brand px-8 font-bold text-brand-foreground transition hover:bg-brand-dark sm:flex-none"
                >
                    {{ __('কার্ট-এ যোগ করুন') }}
                </button>
                <button
                    type="button"
                    data-rc-buy-now="{{ $activeProd['id'] }}"
                    data-checkout-url="{{ $checkoutUrl }}"
                    class="h-12 min-w-44 flex-1 rounded-md border-2 border-brand px-8 font-bold text-brand transition hover:bg-brand hover:text-brand-foreground sm:flex-none"
                >
                    {{ __('ক্যাশ অন ডেলিভারিতে অর্ডার করুন') }}
                </button>
            </div>

            <ul class="mt-6 grid gap-3 rounded-lg border border-line p-4 sm:grid-cols-2">
                <li class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-full bg-brand-soft text-brand">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/></svg>
                    </span>
                    <span>
                        <span class="block text-sm font-bold">Genuine product</span>
                        <span class="block text-xs text-ink/55">Sold and shipped by ReadyCart</span>
                    </span>
                </li>
                <li class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-full bg-brand-soft text-brand">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                    </span>
                    <span>
                        <span class="block text-sm font-bold">Est. delivery</span>
                        <span class="block text-xs text-ink/55">3-4 business days</span>
                    </span>
                </li>
                <li class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-full bg-brand-soft text-brand">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                    </span>
                    <span>
                        <span class="block text-sm font-bold">Easy returns</span>
                        <span class="block text-xs text-ink/55">7-day return window</span>
                    </span>
                </li>
                <li class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-full bg-brand-soft text-brand">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                    </span>
                    <span>
                        <span class="block text-sm font-bold">Secure checkout</span>
                        <span class="block text-xs text-ink/55">Encrypted payments</span>
                    </span>
                </li>
            </ul>
        </div>
    </div>

    {{-- Tabs --}}
    <section class="mt-12" aria-label="Product information">
        <div role="tablist" class="flex gap-6 border-b border-line">
            <button type="button" role="tab" data-rc-tab="description" aria-selected="true" class="-mb-px border-b-2 border-brand pb-3 text-sm font-bold capitalize text-brand">
                Description
            </button>
            <button type="button" role="tab" data-rc-tab="reviews" aria-selected="false" class="-mb-px border-b-2 border-transparent pb-3 text-sm font-bold capitalize text-ink/55">
                Reviews <span class="ml-1 text-ink/40">({{ $activeProd['reviews'] }})</span>
            </button>
        </div>

        <div data-rc-tab-panel="description" class="max-w-4xl space-y-6 py-8 text-sm leading-relaxed text-ink/70">
            @if(isset($product) && (!empty($product->description) || !empty($product->detail) || !empty($product->specification)))
                <div class="prose max-w-none text-ink/80 leading-relaxed">
                    {!! !empty($product->description) ? $product->description : (!empty($product->detail) ? $product->detail : $product->specification) !!}
                </div>
            @else
                <div>
                    <h2 class="mb-2 text-base font-bold text-ink">বিবরণ (Summary)</h2>
                    <p>এই পণ্যটি মানসম্মত উপকরণ দিয়ে তৈরি এবং টেকসই ও নির্ভরযোগ্য ব্যবহারের জন্য ডিজাইন করা হয়েছে। দৈনন্দিন ব্যবহারের জন্য এটি অত্যন্ত সুবিধাজনক ও আকর্ষণীয়।</p>
                </div>
            @endif
        </div>

        <ul data-rc-tab-panel="reviews" class="hidden max-w-3xl space-y-4 py-8">
            @foreach (['Great quality, arrived quickly.', 'Looks exactly like the photos.', 'Good value for the price.'] as $idx => $revText)
                <li class="rounded-md border border-line p-4">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="flex size-8 items-center justify-center rounded-full bg-brand-soft font-bold text-brand">{{ ['A', 'B', 'C'][$idx] }}</span>
                        <span class="font-semibold">Verified buyer</span>
                        <svg class="ml-auto size-4 fill-amber-400 text-amber-400" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <span class="font-bold">{{ 5 - ($idx === 2 ? 1 : 0) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-ink/70">{{ $revText }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Related Products --}}
    <section class="mt-6" aria-label="Related products">
        <div class="mb-5 flex items-end justify-between">
            <h2 class="text-xl font-extrabold">You may also like</h2>
            <a href="{{ $productsUrl }}" class="text-sm font-semibold text-brand">View all</a>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            @foreach ($related as $relProd)
                <x-product-card :store="$store" :product="$relProd" />
            @endforeach
        </div>
    </section>
</main>


