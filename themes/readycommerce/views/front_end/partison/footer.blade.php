@php
    $storeSlug = $slug ?? ($store->slug ?? request()->route('storeSlug') ?? 'stylique');
    $homeUrl = route('landing_page', $storeSlug);
    $productsUrl = route('page.product-list', ['storeSlug' => $storeSlug]);
    $contactUrl = Route::has('page.contact_us') ? route('page.contact_us', ['storeSlug' => $storeSlug]) : $productsUrl;
    $faqUrl = Route::has('page.faq') ? route('page.faq', ['storeSlug' => $storeSlug]) : $productsUrl;
    $blogUrl = Route::has('page.blog') ? route('page.blog', ['storeSlug' => $storeSlug]) : $productsUrl;

    $socials = [
        ['name' => 'Facebook', 'src' => 'facebook'],
        ['name' => 'LinkedIn', 'src' => 'linkedin'],
        ['name' => 'Instagram', 'src' => 'instagram'],
        ['name' => 'YouTube', 'src' => 'youtube'],
        ['name' => 'WhatsApp', 'src' => 'whatsapp'],
        ['name' => 'X', 'src' => 'x'],
        ['name' => 'Telegram', 'src' => 'telegram'],
        ['name' => 'Google', 'src' => 'google'],
    ];

    $payments = [
        ['name' => 'Stripe', 'src' => 'stripe'],
        ['name' => 'PayPal', 'src' => 'paypal-wordmark'],
        ['name' => 'Razorpay', 'src' => 'razorpay'],
        ['name' => 'Paystack', 'src' => 'paystack'],
        ['name' => 'aamarPay', 'src' => 'aamarpay'],
        ['name' => 'bKash', 'src' => 'bkash'],
        ['name' => 'PayTabs', 'src' => 'paytabs'],
        ['name' => 'PayUbiz', 'src' => 'payubiz'],
        ['name' => 'Cashfree', 'src' => 'cashfree'],
        ['name' => 'JazzCash', 'src' => 'jazzcash'],
        ['name' => 'Nagad', 'src' => 'nagad'],
        ['name' => 'SSLCommerz', 'src' => 'sslcommerz'],
    ];
@endphp

<footer class="mt-20 bg-brand-soft">
    <div class="mx-auto grid max-w-[1400px] gap-10 px-3 py-14 sm:px-4 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr_1.1fr] lg:px-6">
        {{-- Column 1: Brand & Contact --}}
        <div>
            <a href="{{ $homeUrl }}" class="flex items-center gap-2" aria-label="Ready eCommerce home">
                <img src="{{ asset('readycart/logo-mark.png') }}" alt="" width="40" height="40" class="brand-logo size-10 shrink-0 object-contain" />
                <span class="text-[15px] font-extrabold leading-[1.05] tracking-tight text-ink">
                    Ready<br />eCommerce
                </span>
            </a>
            <p class="mt-4 max-w-xs text-sm leading-relaxed text-ink/65">
                The ultimate all-in-one solution for your eCommerce business worldwide.
            </p>
            <ul class="mt-5 space-y-3 text-sm text-ink/75">
                <li class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-full bg-brand/10 text-brand">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </span>
                    +880123456789
                </li>
                <li class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-full bg-brand/10 text-brand">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </span>
                    admin@example.com
                </li>
            </ul>
            <ul class="mt-5 flex max-w-[260px] flex-wrap gap-2">
                @foreach ($socials as $social)
                    <li>
                        <a href="#" aria-label="{{ $social['name'] }}" class="flex size-9 items-center justify-center rounded-md bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <img src="{{ asset('readycart/brands/' . $social['src'] . '.svg') }}" alt="" width="18" height="18" class="size-[18px]" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Column 2: Shop --}}
        <div>
            <h3 class="text-lg font-bold text-brand-dark">Shop</h3>
            <ul class="mt-4 space-y-3.5 text-sm text-ink/70">
                <li>
                    <a href="{{ $productsUrl }}" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                        All Products
                    </a>
                </li>
                <li>
                    <a href="{{ $productsUrl }}" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                        Digital Products
                    </a>
                </li>
                <li>
                    <a href="{{ $productsUrl }}?sort=popular" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        Top Rated Products
                    </a>
                </li>
                <li>
                    <a href="{{ $productsUrl }}" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg>
                        Best Deals
                    </a>
                </li>
            </ul>
        </div>

        {{-- Column 3: Customer Service --}}
        <div>
            <h3 class="text-lg font-bold text-brand-dark">Customer Service</h3>
            <ul class="mt-4 space-y-3.5 text-sm text-ink/70">
                <li>
                    <a href="{{ $contactUrl }}" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5Zm0 0a9 9 0 1 1 18 0m0 0v5a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3Z"/><path d="M21 16v2a4 4 0 0 1-4 4h-5"/></svg>
                        Help Center
                    </a>
                </li>
                <li>
                    <a href="{{ $faqUrl }}" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                        Easy Returns
                    </a>
                </li>
                <li>
                    <a href="{{ $faqUrl }}" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                        Shipping Policy
                    </a>
                </li>
                <li>
                    <a href="{{ $faqUrl }}" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        Payment Methods
                    </a>
                </li>
                <li>
                    <a href="{{ $faqUrl }}" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        FAQ
                    </a>
                </li>
                <li>
                    <a href="{{ $contactUrl }}" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        Contact Us
                    </a>
                </li>
            </ul>
        </div>

        {{-- Column 4: Company --}}
        <div>
            <h3 class="text-lg font-bold text-brand-dark">Company</h3>
            <ul class="mt-4 space-y-3.5 text-sm text-ink/70">
                <li>
                    <a href="{{ $contactUrl }}" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        About Us
                    </a>
                </li>
                <li>
                    <a href="{{ $blogUrl }}" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                        Blog
                    </a>
                </li>
                <li>
                    <a href="{{ $faqUrl }}" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 12h-5"/><path d="M15 8h-5"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/></svg>
                        Terms &amp; Conditions
                    </a>
                </li>
                <li>
                    <a href="{{ $faqUrl }}" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                        Privacy Policy
                    </a>
                </li>
                <li>
                    <a href="{{ $faqUrl }}" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5a5.5 5.5 0 0 1-5.5 5.5H11"/></svg>
                        Return &amp; Refund Policy
                    </a>
                </li>
                <li>
                    <a href="{{ $contactUrl }}" class="flex items-center gap-2.5 transition hover:text-brand">
                        <svg class="size-4 text-brand/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"/></svg>
                        Become a Seller
                    </a>
                </li>
            </ul>
        </div>

        {{-- Column 5: Download Our App --}}
        <div>
            <h3 class="text-lg font-bold text-brand-dark">Download Our App</h3>
            <div class="mt-4 flex flex-col gap-3">
                <a href="#" aria-label="Download on the App Store">
                    <img src="{{ asset('readycart/brands/app-store.svg') }}" alt="" width="140" height="42" class="h-[42px] w-auto" />
                </a>
                <a href="#" aria-label="Get it on Google Play">
                    <img src="{{ asset('readycart/brands/google-play.svg') }}" alt="" width="140" height="42" class="h-[42px] w-auto" />
                </a>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-[1400px] border-t border-brand/10 px-3 py-6 sm:px-4 lg:px-6">
        <p class="text-sm font-bold text-ink">Accepted Payment Methods</p>
        <ul class="mt-3 flex flex-wrap gap-2.5">
            @foreach ($payments as $payment)
                <li class="flex h-11 w-[88px] items-center justify-center rounded-md bg-white px-2.5 shadow-sm">
                    <img src="{{ asset('readycart/brands/' . $payment['src'] . '.svg') }}" alt="{{ $payment['name'] }}" width="80" height="28" class="h-6 w-full object-contain" />
                </li>
            @endforeach
        </ul>
        <p class="mt-6 text-center text-sm text-ink/55">&copy; 2026 All right reserved by company</p>
    </div>
</footer>

