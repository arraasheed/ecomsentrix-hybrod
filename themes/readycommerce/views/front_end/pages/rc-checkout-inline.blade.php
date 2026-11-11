@php extract(include base_path('themes/readycommerce/views/components/readycart-data.php')); @endphp
@php
    $storeSlug = $slug ?? ($store->slug ?? request()->route('storeSlug') ?? 'stylique');
    $homeUrl = route('landing_page', $storeSlug);
    $productsUrl = route('page.product-list', ['storeSlug' => $storeSlug]);
    $inputClass = 'h-11 w-full rounded-md border border-line bg-white px-4 text-sm outline-none transition placeholder:text-ink/35 focus:border-brand focus:ring-4 focus:ring-brand/10';

    $serverCart = \App\Models\Cart::cart_list_cookie([], $store->id ?? null);
    $cartData = $serverCart['data'] ?? [];
    $cartProducts = $cartData['product_list'] ?? [];
    $subTotal = (float) ($cartData['sub_total'] ?? ($cartData['final_price'] ?? 0));
    $defaultShipping = 70.00;
    $initialTotal = $subTotal > 0 ? ($subTotal + $defaultShipping) : ($rcProducts[0]['price'] + $defaultShipping);
    $itemCount = count($cartProducts) > 0 ? count($cartProducts) : 1;
@endphp

<main class="mx-auto max-w-[1400px] px-3 pb-12 sm:px-4 lg:px-6" data-rc-checkout-root>
    <div data-rc-checkout-form-wrap>
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="mb-5 flex items-center gap-1.5 text-xs text-ink/55">
            <a href="{{ $homeUrl }}" aria-label="Home" class="hover:text-brand">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
            </a>
            <span class="flex items-center gap-1.5">
                <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                <a href="{{ $productsUrl }}" class="hover:text-brand">শপ</a>
            </span>
            <span class="flex items-center gap-1.5">
                <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                <span class="font-semibold text-ink">অর্ডার চেকআউট</span>
            </span>
        </nav>

        <form action="{{ route('payment.process', $storeSlug) }}" method="POST" data-turbo="false" id="rc-order-form" class="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_420px]">
            @csrf
            {{-- Hidden required fields for Laravel backend order validator --}}
            <input type="hidden" name="billing_info[lastname]" value="." />
            <input type="hidden" name="billing_info[billing_country]" value="19" />
            <input type="hidden" name="billing_info[delivery_country]" value="19" />
            <input type="hidden" name="billing_info[delivery_address]" id="delivery_address_sync" value="" />
            <input type="hidden" name="billing_info[delivery_city]" id="delivery_city_sync" value="Dhaka" />
            <input type="hidden" name="delivery_not_same" value="0" />
            <input type="hidden" name="method_id" value="0" />
            <input type="hidden" name="shipping_final_price" id="rc-shipping-input" value="70" />
            <input type="hidden" name="delivery_price" id="rc-delivery-price-input" value="70" />
            <input type="hidden" name="payment_comment" id="payment_comment_input" value="Cash on Delivery (COD)" />

            <div class="space-y-6">
                {{-- Order Items Section --}}
                <section class="rounded-lg border border-line bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-extrabold text-ink">আপনার অর্ডার করা পণ্য</h2>
                        <span class="rounded-full bg-brand-soft px-3 py-1 text-xs font-bold text-brand">{{ $itemCount }} টি আইটেম</span>
                    </div>
                    <ul class="mt-5 divide-y divide-line" data-rc-checkout-items>
                        @if (!empty($cartProducts))
                            @foreach ($cartProducts as $cItem)
                                @php
                                    $pName = is_object($cItem) ? ($cItem->product_name ?? 'Product') : ($cItem['product_name'] ?? 'Product');
                                    $pPrice = (float) (is_object($cItem) ? ($cItem->price ?? 0) : ($cItem['price'] ?? 0));
                                    $pQty = (int) (is_object($cItem) ? ($cItem->qty ?? 1) : ($cItem['qty'] ?? 1));
                                    $pImg = is_object($cItem) ? ($cItem->image ?? asset('readycart/p-heater.png')) : ($cItem['image'] ?? asset('readycart/p-heater.png'));
                                @endphp
                                <li class="flex items-center gap-4 py-4">
                                    <div class="size-16 shrink-0 overflow-hidden rounded-md bg-surface border border-line">
                                        <img src="{{ $pImg }}" alt="{{ $pName }}" class="size-full object-contain p-1.5 mix-blend-multiply" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-ink">{{ $pName }}</p>
                                        <p class="text-xs text-ink/60">পরিমাণ: {{ $pQty }} টি × ৳{{ number_format($pPrice, 2) }}</p>
                                    </div>
                                    <span class="w-24 text-right text-sm font-extrabold text-brand">৳{{ number_format($pPrice * $pQty, 2) }}</span>
                                </li>
                            @endforeach
                        @else
                            {{-- Fallback preview line --}}
                            <li class="flex items-center gap-4 py-4">
                                <div class="size-16 shrink-0 overflow-hidden rounded-md bg-surface border border-line">
                                    <img src="{{ $rcProducts[0]['image'] }}" alt="{{ $rcProducts[0]['name'] }}" class="size-full object-contain p-1.5 mix-blend-multiply" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $rcProducts[0]['name'] }}</p>
                                    <p class="text-xs text-ink/60">পরিমাণ: ১ টি</p>
                                </div>
                                <span class="w-24 text-right text-sm font-extrabold text-brand">৳{{ number_format($rcProducts[0]['price'], 2) }}</span>
                            </li>
                        @endif
                    </ul>
                </section>

                {{-- Customer Info Section --}}
                <section class="rounded-lg border border-line bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-extrabold text-ink">গ্রাহকের তথ্য ও ডেলিভারি ঠিকানা</h2>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-ink">আপনার নাম <span class="text-brand">*</span></span>
                            <input name="billing_info[firstname]" id="rc-name-input" required placeholder="সম্পূর্ণ নাম লিখুন" autocomplete="name" class="{{ $inputClass }}" />
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-ink">মোবাইল নম্বর <span class="text-brand">*</span></span>
                            <input name="billing_info[billing_user_telephone]" type="tel" required placeholder="01XXXXXXXXX" autocomplete="tel" class="{{ $inputClass }}" id="rc-phone-input" data-slug="{{ $storeSlug }}" />
                            <div id="fraud-check-status" class="mt-2 hidden"></div>
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="mb-2 block text-sm font-semibold text-ink">ইমেইল ঠিকানা <span class="text-xs font-normal text-ink/50">(ঐচ্ছিক)</span></span>
                            <input name="billing_info[email]" id="rc-email-input" type="email" placeholder="example@mail.com" autocomplete="email" class="{{ $inputClass }}" />
                        </label>
                        <div class="sm:col-span-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-ink">সম্পূর্ণ ঠিকানা (বাসা/রোড নং, এলাকা, থানা) <span class="text-brand">*</span></span>
                                <input name="billing_info[billing_address]" id="rc-address-input" required placeholder="যেমন: বাসা নং ১২, রোড নং ৫, ধানমন্ডি, ঢাকা" autocomplete="street-address" class="{{ $inputClass }}" />
                            </label>
                        </div>

                        {{-- Delivery Area Selector (Inside Dhaka vs Outside Dhaka) --}}
                        <div class="sm:col-span-2">
                            <span class="mb-2 block text-sm font-semibold text-ink">ডেলিভারি এরিয়া (Delivery Area) <span class="text-brand">*</span></span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" role="radiogroup">
                                <label class="rc-area-card flex cursor-pointer items-center justify-between p-4 rounded-lg border-2 border-brand bg-brand-soft transition">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="delivery_zone" value="inside" data-cost="70" data-city="Dhaka" checked class="accent-brand rc-delivery-radio" />
                                        <span class="text-sm font-bold text-ink">ঢাকার ভিতরে</span>
                                    </div>
                                    <span class="text-sm font-extrabold text-brand">৳৭০</span>
                                </label>
                                <label class="rc-area-card flex cursor-pointer items-center justify-between p-4 rounded-lg border-2 border-line bg-white transition hover:border-brand/40">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="delivery_zone" value="outside" data-cost="130" data-city="Outside Dhaka" class="accent-brand rc-delivery-radio" />
                                        <span class="text-sm font-bold text-ink">ঢাকার বাইরে</span>
                                    </div>
                                    <span class="text-sm font-extrabold text-ink/70">৳১৩০</span>
                                </label>
                            </div>
                            <input type="hidden" name="billing_info[billing_city]" id="rc-billing-city" value="Dhaka" />
                        </div>
                    </div>
                </section>

                {{-- Special Instruction --}}
                <section class="rounded-lg border border-line bg-white p-6 shadow-sm">
                    <label for="note" class="mb-2 block text-base font-bold text-ink">
                        অতিরিক্ত মন্তব্য বা নির্দেশনা <span class="text-sm font-normal text-ink/50">(ঐচ্ছিক)</span>
                    </label>
                    <textarea id="note" name="additional_note" rows="3" placeholder="ডেলিভারি সংক্রান্ত কোনো বিশেষ নির্দেশনা থাকলে এখানে লিখুন..." class="{{ $inputClass }} h-auto py-3"></textarea>
                </section>
            </div>

            {{-- Right Sticky Order Summary & Payment Method --}}
            <aside class="space-y-4 lg:sticky lg:top-4">
                <section class="rounded-lg border border-line bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-extrabold text-ink">অর্ডারের সারসংক্ষেপ</h2>
                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-ink/65">সাবটোটাল (Subtotal)</dt>
                            <dd class="font-semibold text-ink" id="rc-subtotal-val" data-subtotal="{{ $subTotal > 0 ? $subTotal : $rcProducts[0]['price'] }}">৳{{ number_format($subTotal > 0 ? $subTotal : $rcProducts[0]['price'], 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink/65">ডেলিভারি চার্জ</dt>
                            <dd class="font-semibold text-ink" id="rc-shipping-val">৳70.00</dd>
                        </div>
                        <div class="flex justify-between border-t border-line pt-4 text-base">
                            <dt class="font-bold text-ink">সর্বমোট প্রদেয় (Total)</dt>
                            <dd class="font-extrabold text-brand text-xl" id="rc-total-val">৳{{ number_format($initialTotal, 2) }}</dd>
                        </div>
                    </dl>

                    {{-- Payment Method Selection --}}
                    <fieldset class="mt-6 space-y-3">
                        <legend class="mb-3 text-sm font-bold text-ink">মূল্য পরিশোধের মাধ্যম (Payment Method)</legend>
                        
                        {{-- COD --}}
                        <label class="rc-payment-card flex cursor-pointer items-center gap-3 rounded-md border-2 border-brand bg-brand-soft p-3.5 transition">
                            <input type="radio" name="payment_type" value="cod" checked class="accent-brand rc-pay-type" />
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-white text-brand shadow-xs">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                            </span>
                            <span class="flex-1">
                                <span class="block text-sm font-bold text-ink">ক্যাশ অন ডেলিভারি (COD)</span>
                                <span class="block text-xs text-ink/60">পণ্য হাতে পেয়ে টাকা পরিশোধ করুন</span>
                            </span>
                        </label>

                        {{-- bKash / Nagad Send Money --}}
                        <label class="rc-payment-card flex cursor-pointer items-center gap-3 rounded-md border-2 border-line p-3.5 transition hover:border-brand/40">
                            <input type="radio" name="payment_type" value="cod" data-method="bkash" class="accent-brand rc-pay-type" />
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-pink-100 text-pink-600 font-extrabold text-xs shadow-xs">
                                ৳ Pay
                            </span>
                            <span class="flex-1">
                                <span class="block text-sm font-bold text-ink">বিকাশ / নগদ (Send Money)</span>
                                <span class="block text-xs text-ink/60">সেন্ড মানি করে TrxID দিয়ে অর্ডার করুন</span>
                            </span>
                        </label>

                        {{-- bKash instructions & TrxID Input --}}
                        <div id="rc-bkash-box" class="hidden rounded-lg border border-pink-200 bg-pink-50/70 p-4 space-y-3 text-sm">
                            <div class="rounded-md bg-white p-3 border border-pink-100 text-xs leading-relaxed text-ink/80">
                                <p class="font-bold text-pink-700">বিকাশ / নগদ পার্সোনাল নম্বর:</p>
                                <p class="text-base font-extrabold text-ink mt-0.5 tracking-wider">{{ $themeSettings['header_hotline_number'] ?? '01700000000' }}</p>
                                <p class="text-[11px] text-ink/60 mt-1">উক্ত নম্বরে সর্বমোট টাকা Send Money করে নিচের ঘরে প্রেরক নম্বর ও TrxID লিখে অর্ডার সম্পন্ন করুন।</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-ink mb-1">আপনার বিকাশ/নগদ নম্বর</label>
                                <input type="tel" id="rc_bkash_sender" placeholder="01XXXXXXXXX" class="{{ $inputClass }} h-10 text-xs" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-ink mb-1">Transaction ID (TrxID)</label>
                                <input type="text" id="rc_bkash_trx" placeholder="যেমন: 9J72KL90" class="{{ $inputClass }} h-10 text-xs uppercase" />
                            </div>
                        </div>
                    </fieldset>

                    <button
                        type="submit"
                        id="rc-submit-order-btn"
                        class="mt-6 flex h-13 w-full items-center justify-center gap-2 rounded-md bg-brand text-base font-bold text-brand-foreground shadow-md transition hover:bg-brand-dark"
                    >
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        অর্ডার নিশ্চিত করুন (Confirm Order)
                    </button>
                </section>
            </aside>
        </form>
    </div>
</main>

@include('front_end.hooks.checkout_form')

{{-- Checkout calculation & validation script --}}
<script>
(function() {
    function initRcCheckout() {
        var form = document.getElementById('rc-order-form');
        if (!form) return;

        var subtotalEl = document.getElementById('rc-subtotal-val');
        var shippingEl = document.getElementById('rc-shipping-val');
        var totalEl = document.getElementById('rc-total-val');
        var shippingInput = document.getElementById('rc-shipping-input');
        var deliveryPriceInput = document.getElementById('rc-delivery-price-input');
        var cityInput = document.getElementById('rc-billing-city');
        var deliveryAddressSync = document.getElementById('delivery_address_sync');
        var addressInput = document.getElementById('rc-address-input');
        var phoneInput = document.getElementById('rc-phone-input');
        var emailInput = document.getElementById('rc-email-input');
        var commentInput = document.getElementById('payment_comment_input');

        var subtotal = parseFloat(subtotalEl ? subtotalEl.getAttribute('data-subtotal') || '0' : '0');

        // Delivery Area change
        var areaRadios = document.querySelectorAll('.rc-delivery-radio');
        areaRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                var cost = parseFloat(this.getAttribute('data-cost') || '70');
                var city = this.getAttribute('data-city') || 'Dhaka';
                if (shippingInput) shippingInput.value = cost;
                if (deliveryPriceInput) deliveryPriceInput.value = cost;
                if (cityInput) cityInput.value = city;
                if (shippingEl) shippingEl.textContent = '৳' + cost.toFixed(2);
                if (totalEl) totalEl.textContent = '৳' + (subtotal + cost).toFixed(2);

                document.querySelectorAll('.rc-area-card').forEach(function(c) {
                    c.classList.remove('border-brand', 'bg-brand-soft');
                    c.classList.add('border-line', 'bg-white');
                });
                var parent = radio.closest('.rc-area-card');
                if (parent) {
                    parent.classList.add('border-brand', 'bg-brand-soft');
                    parent.classList.remove('border-line', 'bg-white');
                }
            });
        });

        // Payment Method change (COD vs bKash)
        var payRadios = document.querySelectorAll('.rc-pay-type');
        var bkashBox = document.getElementById('rc-bkash-box');
        payRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                var isBkash = this.getAttribute('data-method') === 'bkash';
                if (bkashBox) {
                    bkashBox.classList.toggle('hidden', !isBkash);
                }
                document.querySelectorAll('.rc-payment-card').forEach(function(c) {
                    c.classList.remove('border-brand', 'bg-brand-soft');
                    c.classList.add('border-line');
                });
                var parent = radio.closest('.rc-payment-card');
                if (parent) {
                    parent.classList.add('border-brand', 'bg-brand-soft');
                    parent.classList.remove('border-line');
                }
            });
        });

        // Address & Email auto sync before submit
        form.addEventListener('submit', function(e) {
            if (addressInput && deliveryAddressSync) {
                deliveryAddressSync.value = addressInput.value.trim();
            }
            if (emailInput && (!emailInput.value || !emailInput.value.trim())) {
                var phoneVal = phoneInput ? phoneInput.value.replace(/[^0-9]/g, '') : 'customer';
                emailInput.value = 'guest_' + (phoneVal || Date.now()) + '@ecommercego.dev';
            }
            var activePay = document.querySelector('.rc-pay-type:checked');
            if (activePay && activePay.getAttribute('data-method') === 'bkash') {
                var sender = (document.getElementById('rc_bkash_sender')?.value || '').trim();
                var trx = (document.getElementById('rc_bkash_trx')?.value || '').trim();
                if (commentInput) {
                    commentInput.value = 'Paid via bKash/Nagad (Send Money) | Sender: ' + (sender || 'N/A') + ' | TrxID: ' + (trx || 'N/A');
                }
            } else {
                if (commentInput) {
                    commentInput.value = 'Cash on Delivery (COD)';
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRcCheckout);
    } else {
        initRcCheckout();
    }
    document.addEventListener('turbo:load', initRcCheckout);
})();
</script>
