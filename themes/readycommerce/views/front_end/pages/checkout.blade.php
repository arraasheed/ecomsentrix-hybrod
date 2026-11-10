@extends('front_end.layouts.app')

@section('page-title')
    {{ $themeSettings['checkout_banner_title'] ?? __('Checkout') }}
@endsection

@section('content')
<main class="rc-container rc-page rc-checkout-page" data-rc-checkout data-address-url="{{ route('order.address.form', $slug) }}">
    <header class="rc-page-heading"><div><span class="rc-kicker">{{ __('Secure checkout') }}</span><h1>{{ $themeSettings['checkout_banner_title'] ?? __('Checkout') }}</h1><p>{{ __('Add your details and choose a payment method to complete your order.') }}</p></div></header>

    @if (($themeSettings['checkout_status'] ?? 1) == 1)
    <form method="POST" action="{{ route('payment.process', $store->slug) }}" enctype="multipart/form-data" id="formdata" data-turbo="false" class="rc-checkout-grid">
        @csrf
        <div class="rc-checkout-stack">
            <section class="rc-card-panel rc-form-section" id="billing_info" aria-labelledby="rc-customer-info-title">
                <h2 id="rc-customer-info-title">1. {{ __('Customer information') }}</h2>
                @include('front_end.hooks.checkout_hide_field')
                @php $customer = Auth::guard('customers')->user(); @endphp
                <div class="rc-form-grid">
                    <label class="rc-form-field" for="first-name"><span>{{ __('First name') }} <span aria-hidden="true">*</span></span><input id="first-name" name="billing_info[firstname]" value="{{ old('billing_info.firstname', $customer->first_name ?? '') }}" autocomplete="given-name" required></label>
                    <label class="rc-form-field" for="last-name"><span>{{ __('Last name') }} <span aria-hidden="true">*</span></span><input id="last-name" name="billing_info[lastname]" value="{{ old('billing_info.lastname', $customer->last_name ?? '') }}" autocomplete="family-name" required></label>
                    <label class="rc-form-field" for="email"><span>{{ __('Email address') }} <span aria-hidden="true">*</span></span><input id="email" type="email" name="billing_info[email]" value="{{ old('billing_info.email', $customer->email ?? '') }}" autocomplete="email" required></label>
                    <label class="rc-form-field" for="phone"><span>{{ __('Phone number') }} <span aria-hidden="true">*</span></span><input id="phone" type="tel" inputmode="tel" name="billing_info[billing_user_telephone]" value="{{ old('billing_info.billing_user_telephone', $customer->mobile ?? '') }}" autocomplete="tel" required></label>
                </div>
                @if (\App\Models\Utility::CustomerAuthCheck($store->slug) != true)
                    <label class="rc-check-row" style="margin-top:16px"><input type="checkbox" id="register" name="register" value="1"><span>{{ __('Create an account for faster checkout and order tracking') }}</span></label>
                @endif
            </section>

            <section class="rc-card-panel rc-form-section" aria-labelledby="rc-billing-title">
                <h2 id="rc-billing-title">2. {{ __('Billing address') }}</h2>
                <div class="selling_address_form"></div>
            </section>

            <section class="rc-card-panel rc-form-section" aria-labelledby="rc-shipping-title">
                <h2 id="rc-shipping-title">3. {{ __('Shipping address') }}</h2>
                <label class="rc-check-row"><input type="checkbox" id="same-address" name="delivery_not_same" value="1"><span>{{ __('Ship to a different address') }}</span></label>
                <div class="shipping_address_form" aria-live="polite"></div>
            </section>

            <section class="rc-card-panel rc-form-section" aria-labelledby="rc-payment-title">
                <h2 id="rc-payment-title">4. {{ __('Payment method') }}</h2>
                <input type="hidden" name="payment_type" class="payment_types" value="cod">
                @php
                    $selectedPayment = collect($payments ?? [])->first(fn ($payment) => ($payment['status'] ?? 'off') === 'on');
                    $selectedPaymentName = $selectedPayment['name'] ?? 'cod';
                @endphp
                <div class="rc-checkout-stack">
                    @forelse (($payments ?? []) as $key => $payment)
                        @if (($payment['status'] ?? 'off') === 'on')
                            <label class="rc-payment-option">
                                <input type="radio" name="payment_setting_id" value="{{ $payment['name'] }}" @checked(($payment['name'] ?? '') === $selectedPaymentName)>
                                <span><strong>{{ $payment['name_string'] ?? $payment['name'] }}</strong><br><span>{{ $payment['detail'] ?? '' }}</span></span>
                            </label>
                            @if (($payment['name'] ?? '') === 'bank_transfer')
                                <label class="rc-form-field" for="payment-receipt"><span>{{ __('Payment receipt') }}</span><input id="payment-receipt" type="file" name="payment_receipt" class="bank_transfer_receipt" accept="image/*,.pdf"></label>
                            @endif
                            @if (($payment['name'] ?? '') === 'whatsapp')
                                <label class="rc-form-field" for="wts_number"><span>{{ __('WhatsApp number') }}</span><input id="wts_number" name="wts_number" type="tel" value="{{ old('wts_number') }}" autocomplete="tel"></label>
                            @endif
                        @endif
                    @empty
                        <p class="rc-form-note">{{ __('No payment methods are available right now.') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="rc-card-panel rc-form-section" aria-labelledby="rc-order-note-title">
                <h2 id="rc-order-note-title">5. {{ __('Order notes') }} <span class="rc-form-note">({{ __('optional') }})</span></h2>
                <label class="rc-form-field" for="payment-comment"><span>{{ __('Add a note to your order') }}</span><textarea id="payment-comment" name="payment_comment" rows="4" placeholder="{{ __('Anything we should know?') }}">{{ old('payment_comment') }}</textarea></label>
            </section>

            @stack('CustomFieldView')
            @stack('addCheckoutAttachment')
            @if (isset($settings['additional_notes']) && $settings['additional_notes'] === 'on')
                <section class="rc-card-panel rc-form-section"><h2>{{ __('Additional details') }}</h2><div class="additional_notes" aria-live="polite"></div></section>
            @endif
        </div>

        <aside class="rc-card-panel rc-checkout-summary" aria-labelledby="rc-order-summary-title">
            <h2 id="rc-order-summary-title">{{ __('Order summary') }}</h2>
            <div class="checkout_page_cart">
                <div class="checkout-page-right">
                    @php
                        $checkoutItems = collect($response->data->product_list ?? []);
                        $checkoutSubtotal = (float) ($response->data->sub_total ?? $response->data->final_price ?? 0);
                    @endphp
                    @foreach ($checkoutItems as $item)
                        <div class="rc-summary-row"><span>{{ $item->name }} × {{ $item->qty }}</span><strong>{!! \App\Models\Product::ManageCartPrice($item, $store) !!}</strong></div>
                    @endforeach
                    <div class="rc-summary-row rc-summary-total"><span>{{ __('Subtotal') }}</span><strong>{{ currency_format_with_sym($checkoutSubtotal, $store->id) }}</strong></div>
                </div>
            </div>
            <input type="hidden" name="coupon_code" id="coupon_code" value="{{ old('coupon_code') }}">
            <input type="hidden" name="sub_total" id="sub_total_checkout_page" value="{{ $checkoutSubtotal ?? 0 }}">
            @include('front_end.hooks.checkout_form')
            <button type="submit" class="rc-button rc-button--primary rc-button--full">{{ __('Place order securely') }} <span aria-hidden="true">→</span></button>
            <p class="rc-form-note">{{ __('Your payment is handled by the selected payment provider.') }}</p>
        </aside>
    </form>
    @endif
</main>
@endsection
