@extends('front_end.layouts.app')

@section('page-title')
    {{ $themeSettings['cart_banner_title'] ?? __('Your cart') }}
@endsection

@section('content')
<main class="rc-container rc-page rc-cart-page">
    @if (($themeSettings['cart_banner_status'] ?? 0) == 1)
        <header class="rc-page-banner" @if(!empty($themeSettings['cart_banner_image'])) style="--rc-banner-image: url('{{ get_file($themeSettings['cart_banner_image']) }}')" @endif>
            <span class="rc-kicker">{{ __('Review your selection') }}</span>
            <h1>{{ $themeSettings['cart_banner_title'] ?? __('Your cart') }}</h1>
        </header>
    @else
        <header class="rc-page-heading"><div><span class="rc-kicker">{{ __('Almost there') }}</span><h1>{{ __('Your cart') }}</h1><p>{{ __('Review your items before checkout.') }}</p></div></header>
    @endif
    @if (($themeSettings['cart_status'] ?? 1) == 1)
        <section class="rc-cart-page-section">
            @include('front_end.pages.cart-list')
        </section>
    @endif
</main>
@endsection
