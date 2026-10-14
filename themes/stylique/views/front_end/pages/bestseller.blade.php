<section class="section-spacing collection-sec">
  <div class="md:container mx-auto px-4 w-full">
    <div class="text-center items-center md:mb-10 mb-6 flex flex-wrap gap-3 justify-center lg:justify-between">
      <h2 class="md:text-4xl text-3xl font-bold">
        {!! $themeSettings['bestseller_title'] ?? 'Explore Our Products' !!}
      </h2>
    </div>

    {{-- Demo Theme: Grid Layout --}}
    @if (env('DEMO_THEME_ENABLED', false))
      <div class="tab-content-wrapper">
        <div class="tab-content active" id="all-tab">
          {{-- Responsive Grid: 5 cols (lg) → 4 (md) → 3 (sm) → 2 (xs) --}}
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 lg:gap-4 sm:gap-3 gap-2">
            @foreach(collect($products)->reverse()->take(10) as $product)
              <div class="w-full">
                <x-product-card-demo :store="$store" :product="$product" />
              </div>
            @endforeach
          </div>
        </div>
      </div>

    {{-- Default: Swiper Carousel --}}
    @else
      <div class="tab-content-wrapper">
        <div class="tab-content active" id="all-tab">
          <div class="swiper featured-products">
            <div class="swiper-wrapper">
              @foreach(collect($products)->reverse()->take(10) as $product)
                <div class="swiper-slide">
                  <x-product-card :store="$store" :product="$product" />
                </div>
              @endforeach
            </div>
            <div class="arrow-wrapper">
              <div class="swiper-button-next"></div>
              <div class="swiper-button-prev"></div>
            </div>
          </div>
        </div>
      </div>
    @endif

  </div>
</section>