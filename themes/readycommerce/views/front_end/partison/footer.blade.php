@php
    $footerLogoPath = $themeSettings['header_logo'] ?? '';
    $footerLogo = !empty($footerLogoPath) ? get_file($footerLogoPath) : asset('themes/readycart/assets/images/logo-mark.png');
@endphp
<footer class="rc-footer">
    <div class="rc-container rc-footer__grid">
        <div class="rc-footer__brand">
            <a href="{{ route('landing_page', $slug) }}" class="rc-brand rc-brand--footer">
                <img src="{{ $footerLogo }}" alt="{{ $store->name }}" width="40" height="40" loading="lazy">
                <span>{{ $store->name }}</span>
            </a>
            <p>{{ $themeSettings['footer_description'] ?? __('Thoughtful picks, dependable quality, and a smoother way to shop.') }}</p>
        </div>
        <div>
            <h2>{{ __('Explore') }}</h2>
            <ul>
                <li><a href="{{ route('page.product-list', $slug) }}">{{ __('All products') }}</a></li>
                <li><a href="{{ route('page.cart', $slug) }}">{{ __('Your cart') }}</a></li>
                @auth('customers')
                    <li><a href="{{ route('my-account.index', $slug) }}">{{ __('My account') }}</a></li>
                @else
                    <li><a href="{{ route('customer.login', $slug) }}">{{ __('Sign in') }}</a></li>
                @endauth
            </ul>
        </div>
        <div>
            <h2>{{ __('Customer care') }}</h2>
            <ul>
                <li><a href="{{ route('page.product-list', $slug) }}">{{ __('Shipping information') }}</a></li>
                <li><a href="{{ route('page.product-list', $slug) }}">{{ __('Returns and exchanges') }}</a></li>
                <li><a href="{{ route('page.product-list', $slug) }}">{{ __('Contact') }}</a></li>
            </ul>
        </div>
        <div class="rc-footer__newsletter">
            <h2>{{ __('Stay in the loop') }}</h2>
            <p>{{ __('Get product news and store updates.') }}</p>
            <form action="{{ route('newsletter.store', $slug) }}" method="POST">
                @csrf
                <label class="sr-only" for="rc-newsletter-email">{{ __('Email address') }}</label>
                <input id="rc-newsletter-email" name="email" type="email" autocomplete="email" required placeholder="{{ __('Your email address') }}">
                <button type="submit">{{ __('Subscribe') }}</button>
            </form>
        </div>
    </div>
    <div class="rc-container rc-footer__bottom">
        <p>© {{ date('Y') }} {{ $store->name }}. {{ __('All rights reserved.') }}</p>
        <p>{{ __('Secure checkout') }} <span aria-hidden="true">·</span> {{ __('Trusted delivery') }}</p>
    </div>
</footer>
