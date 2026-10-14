<div class="swiper-wrapper">
    @foreach(collect($all_products)->reverse()->take(10) as $product)
        <div class="swiper-slide">
            @if (env('DEMO_THEME_ENABLED', false))
                <x-product-card-demo :store="$store" :product="$product" />
            @else
                <x-product-card :store="$store" :product="$product" />
            @endif
        </div>
    @endforeach
</div>
<div class="arrow-wrapper">
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
</div>