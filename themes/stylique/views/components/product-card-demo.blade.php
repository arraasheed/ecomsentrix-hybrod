{{-- 
    Product Card - Demo Site Style (Levo Leather reference)
    CLAUDE-DEMO Phase 2: UI Redesign
    
    Kill-switch: DEMO_THEME_ENABLED=false in .env
    
    Props:
      - product: Product model
      - store: Store model
      - showDiscount: bool (default: true) - show discount percentage badge
      - showRating: bool (default: false) - show star rating (if future feature)
--}}
@props(['product', 'store', 'showDiscount' => true, 'showRating' => false])

@php
  $productUrl = url($store->slug . '/product/' . $product->slug);
  $imageUrl = get_file($product->cover_image_path);
  
  // Calculate discount percentage if available
  $discountPercent = 0;
  if (isset($product->price) && isset($product->sale_price) && $product->sale_price > 0) {
    $discountPercent = round((($product->price - $product->sale_price) / $product->price) * 100);
  }
@endphp

<a href="{{ $productUrl }}" class="card-container block w-full cursor-pointer text-decoration-none no-underline hover:no-underline" data-turbo="true">
  <div class="card-parent">
    <div class="card-box bg-white rounded-lg overflow-hidden">
      
      {{-- Image Section --}}
      <div class="card-image-box relative">
        <div class="image-area1 flex justify-center items-center p-1">
          <div class="picture-wrapper w-36 h-36 flex items-center justify-center overflow-hidden rounded">
            <img 
              src="{{ $imageUrl }}"
              alt="{{ $product->name }}"
              width="400"
              height="400"
              loading="lazy"
              fetchpriority="auto"
              class="w-full h-full object-cover"
            />
          </div>
        </div>

        {{-- Discount Badge (Top-Left) --}}
        @if ($showDiscount && $discountPercent > 0)
          <div class="product-tag absolute top-2 left-2">
            <span class="tag inline-block text-xs font-bold text-white bg-green-500 rounded px-2 py-1">
              -{{ $discountPercent }}% OFF
            </span>
          </div>
        @endif

        {{-- Wishlist Button (Top-Right) --}}
        <button 
          class="wishlist-btn img-wishlist absolute top-2 right-2 bg-white rounded-full p-2 border border-gray-200 hover:border-gray-400 transition-all duration-300 z-10"
          type="button"
          data-product-id="{{ $product->id }}"
          data-action="{{ $product->in_whishlist ? 'remove' : 'add' }}"
          aria-label="Add to Wishlist"
          onclick="event.preventDefault(); event.stopPropagation();">
          <svg class="w-5 h-5 {{ $product->in_whishlist ? 'fill-red-500' : 'fill-none' }} stroke-current text-gray-600 hover:text-red-500 transition-colors"
            viewBox="0 0 21 21" xmlns="http://www.w3.org/2000/svg">
            <path d="M10.7235 18.291C10.4382 18.3917 9.96839 18.3917 9.68314 18.291C7.25009 17.4604 1.81348 13.9954 1.81348 8.12256C1.81348 5.53011 3.90254 3.43265 6.47822 3.43265C8.00517 3.43265 9.35593 4.17095 10.2033 5.31197C11.0507 4.17095 12.4098 3.43265 13.9284 3.43265C16.5041 3.43265 18.5931 5.53011 18.5931 8.12256C18.5931 13.9954 13.1565 17.4604 10.7235 18.291Z" 
              stroke-width="1.23051" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
        </button>
      </div>

      {{-- Product Info Section --}}
      <div class="card-description-box p-3">
        <div class="card-description-top">
          
          {{-- Product Title --}}
          <div class="product-title text-center mb-2">
            <p class="text-sm font-medium text-gray-900 line-clamp-2 h-9">
              {{ $product->name }}
            </p>
          </div>

          {{-- Star Ratings (Future Enhancement) --}}
          @if ($showRating)
            <div class="ratings flex justify-center items-center gap-1 mb-2">
              <div class="stars text-yellow-400">⭐⭐⭐⭐⭐</div>
              <p class="text-xs text-gray-600">({{ rand(10, 200) }} reviews)</p>
            </div>
          @endif

          {{-- Price Section --}}
          <div class="product-card-bottom mb-3">
            <div class="product-price flex justify-center items-center gap-2">
              @if ($product->variant_product == 0)
                <span class="text-base font-bold text-pink-600">
                  {!! \App\Models\Product::getProductPrice($product, $store) !!}
                </span>
                @if ($discountPercent > 0 && isset($product->price))
                  <span class="old-price text-xs text-gray-400 line-through">
                    {{ $product->price }}
                  </span>
                @endif
              @else
                <span class="text-base font-bold text-gray-600">
                  {{ __('In Variant') }}
                </span>
              @endif
            </div>
          </div>
        </div>

        {{-- Button Row --}}
        <div class="product-card-bottom-2 flex flex-col gap-2">
          <button 
            class="add-cart w-full py-1.5 px-2 text-xs font-semibold text-white bg-pink-600 hover:bg-pink-700 rounded transition-colors duration-200 border border-pink-600"
            type="button"
            data-product-id="{{ $product->id }}"
            data-qty="1"
            data-action="add-to-cart"
            onclick="event.preventDefault(); event.stopPropagation();"
            aria-label="Add to Cart">
            {{ __('Add to Cart') }}
          </button>
          
          <button 
            class="buy-now w-full py-1.5 px-2 text-xs font-semibold text-pink-600 bg-white hover:bg-pink-50 rounded transition-colors duration-200 border border-pink-600"
            type="button"
            data-product-id="{{ $product->id }}"
            data-action="buy-now"
            onclick="event.preventDefault(); event.stopPropagation();"
            aria-label="Buy Now">
            {{ __('Buy Now') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</a>
