@php extract(include base_path('themes/readycommerce/views/components/readycart-data.php')); @endphp
@php
    $storeSlug = $slug ?? ($store->slug ?? request()->route('storeSlug') ?? 'stylique');
    $homeUrl = route('landing_page', $storeSlug);
    $productsUrl = route('page.product-list', ['storeSlug' => $storeSlug]);
    $inputClass = 'h-11 w-full rounded-md border border-line bg-white px-4 text-sm outline-none transition placeholder:text-ink/35 focus:border-brand focus:ring-4 focus:ring-brand/10';
@endphp

<main class="mx-auto max-w-[1400px] px-3 pb-12 sm:px-4 lg:px-6" data-rc-checkout-root>
    {{-- Order Placed State (hidden until submit) --}}
    <div data-rc-order-placed class="mx-auto hidden max-w-xl flex-col items-center px-4 py-24 text-center">
        <svg class="size-16 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
        <h1 class="mt-5 text-3xl font-extrabold">Order placed</h1>
        <p class="mt-2 text-ink/60">Thank you. We will send a confirmation to your email shortly.</p>
        <a href="{{ $homeUrl }}" class="mt-8 inline-flex h-12 items-center justify-center rounded-md bg-brand px-8 font-bold text-brand-foreground hover:bg-brand-dark">
            Continue Shopping
        </a>
    </div>

    <div data-rc-checkout-form-wrap>
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="mb-5 flex items-center gap-1.5 text-xs text-ink/55">
            <a href="{{ $homeUrl }}" aria-label="Home" class="hover:text-brand">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
            </a>
            <span class="flex items-center gap-1.5">
                <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                <span>Cart</span>
            </span>
            <span class="flex items-center gap-1.5">
                <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                <span class="font-semibold text-ink">Checkout</span>
            </span>
        </nav>

        <form data-rc-checkout-form class="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_400px]">
            <div class="space-y-6">
                <section class="rounded-lg border border-line bg-white p-6">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-extrabold">Your Order</h2>
                        <span class="rounded-full bg-brand-soft px-2.5 py-0.5 text-xs font-bold text-brand"><span data-rc-checkout-count>1</span> items</span>
                    </div>
                    <ul class="mt-5 divide-y divide-line" data-rc-checkout-items>
                        <li class="flex items-center gap-4 py-4">
                            <div class="size-16 shrink-0 overflow-hidden rounded-md bg-surface">
                                <img src="{{ $rcProducts[0]['image'] }}" alt="{{ $rcProducts[0]['name'] }}" class="size-full object-contain p-1.5 mix-blend-multiply" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold uppercase tracking-wider text-brand">{{ $rcProducts[0]['brand'] }}</p>
                                <p class="truncate text-sm font-semibold">{{ $rcProducts[0]['name'] }}</p>
                            </div>
                            <span class="w-20 text-right text-sm font-extrabold">${{ number_format($rcProducts[0]['price'], 2) }}</span>
                        </li>
                    </ul>
                </section>

                <section class="rounded-lg border border-line bg-white p-6">
                    <h2 class="text-xl font-extrabold">Shipping Address</h2>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold">Name <span class="text-brand">*</span></span>
                            <input name="name" required placeholder="Enter name" autocomplete="name" class="{{ $inputClass }}" />
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold">Email <span class="text-brand">*</span></span>
                            <input name="email" type="email" required placeholder="Enter email" autocomplete="email" class="{{ $inputClass }}" />
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold">Phone <span class="text-brand">*</span></span>
                            <input name="phone" type="tel" required placeholder="Enter phone" autocomplete="tel" class="{{ $inputClass }}" />
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold">Area</span>
                            <select name="area" class="{{ $inputClass }}">
                                <option>East Clementineberg</option>
                                <option>North Harbor</option>
                                <option>Westfield</option>
                            </select>
                        </label>
                        <div class="sm:col-span-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold">Address Line <span class="text-brand">*</span></span>
                                <input name="address" required placeholder="Enter address" autocomplete="street-address" class="{{ $inputClass }}" />
                            </label>
                        </div>
                    </div>
                    <fieldset class="mt-5">
                        <legend class="mb-2 text-sm font-semibold">Address Tag</legend>
                        <div class="flex gap-2">
                            @foreach (['Home', 'Office', 'Other'] as $idx => $t)
                                <label data-rc-tag-label class="flex cursor-pointer items-center gap-2 rounded-md border px-4 py-2 text-sm font-semibold uppercase transition {{ $idx === 0 ? 'border-brand bg-brand-soft text-brand' : 'border-line text-ink/65' }}">
                                    <input type="radio" name="tag" value="{{ $t }}" @checked($idx === 0) class="accent-brand" />
                                    {{ $t }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                </section>

                <section>
                    <label for="note" class="mb-2 block text-lg font-bold">
                        Note <span class="text-sm font-normal text-ink/50">(Optional)</span>
                    </label>
                    <textarea id="note" name="note" rows="4" placeholder="Write your note here..." class="{{ $inputClass }} h-auto py-3"></textarea>
                </section>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-4">
                <section class="rounded-lg border border-line bg-white p-6">
                    <h2 class="text-xl font-extrabold">Order Summary</h2>
                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-ink/65">Subtotal</dt>
                            <dd class="font-semibold" data-rc-checkout-subtotal>${{ number_format($rcProducts[0]['price'], 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink/65">Shipping Charge</dt>
                            <dd class="font-semibold">$0.00</dd>
                        </div>
                        <div class="flex justify-between border-t border-line pt-4 text-base">
                            <dt class="font-bold">Total Payable</dt>
                            <dd class="font-extrabold text-brand" data-rc-checkout-total>${{ number_format($rcProducts[0]['price'], 2) }}</dd>
                        </div>
                    </dl>

                    <fieldset class="mt-6 space-y-3">
                        <legend class="mb-3 text-sm font-bold">Payment Method</legend>
                        <label data-rc-pay-label class="flex cursor-pointer items-center gap-3 rounded-md border border-brand bg-brand-soft p-3 transition">
                            <input type="radio" name="payment" value="cod" checked class="accent-brand" />
                            <span class="flex size-10 items-center justify-center rounded-lg bg-white text-brand">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                            </span>
                            <span class="flex-1">
                                <span class="block text-sm font-semibold">Cash on delivery</span>
                                <span class="block text-xs text-ink/55">Pay when your order arrives</span>
                            </span>
                        </label>
                        <label data-rc-pay-label class="flex cursor-pointer items-center gap-3 rounded-md border border-line p-3 transition hover:border-brand/40">
                            <input type="radio" name="payment" value="card" class="accent-brand" />
                            <span class="flex size-10 items-center justify-center rounded-lg bg-white text-brand">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                            </span>
                            <span class="flex-1">
                                <span class="block text-sm font-semibold">Credit or Debit Card</span>
                                <span class="block text-xs text-ink/55">Pay securely using your card</span>
                            </span>
                            <svg class="size-4 text-ink/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </label>
                    </fieldset>
                </section>
                <button
                    type="submit"
                    class="h-12 w-full rounded-md bg-brand text-base font-semibold text-brand-foreground transition hover:bg-brand-dark"
                >
                    Place Order
                </button>
            </aside>
        </form>
    </div>
</main>


