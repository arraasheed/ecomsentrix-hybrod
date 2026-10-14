@props(['product', 'store'])

{{-- Phase A — Product Card 3 (demo): 3:4 image, discount badge, pink price, secondary CTA --}}
<div class="flex product-card flex-col h-full bg-white overflow-hidden transition-all duration-300">
        {!! \App\Models\Product::productSalesPage( $store->slug, $product->id, false) !!}
        <div class="relative">
            <a href="{{ url($store->slug . '/product/' . $product->slug) }}" class="block">
                <div class="relative pt-[120%] overflow-hidden bg-[#f6efe7]">
                    <img src="{{ get_file($product->cover_image_path)}}"
                    width="400" height="480"
                    alt="{{ $product->name }}"
                    loading="lazy"
                    class="absolute inset-0 w-full h-full object-cover object-top product-image" />
                </div>
            </a>
            <div class="absolute top-3 right-3">
                <button class="wishlist-btn wishbtn-globaly {{ $product->in_whishlist ? 'active' : ''}} flex bg-white/90 backdrop-blur rounded-full p-2 shadow-sm border border-black/5 hover:bg-white" 
                        product_id="{{ $product->id }}"
                        in_wishlist="{{ $product->in_whishlist ? 'remove' : 'add' }}" aria-label="Wishlist">
                        <i class="far fa-heart text-gray-700 text-sm"></i>
                    </button>
                    {!! \App\Models\Product::actionLinks( $store, $product) !!}
                </div>
            </div>
        <div class="flex flex-col flex-1 p-3 gap-1">
            <h3 class="font-semibold text-[14px] leading-tight line-clamp-2 min-h-[2.6em]">
                <a href="{{ url($store->slug . '/product/' . $product->slug) }}" class="hover:text-[var(--levo-primary)] transition-colors">{{ $product->name }}</a>
            </h3>
            <p class="text-gray-400 text-xs line-clamp-1 hidden sm:block">
                {{ Str::limit(strip_tags($product->description), 60) }}
            </p>
            <div class="flex items-center gap-2 mt-1">
                @if ($product->variant_product == 0)
                    <span class="font-extrabold text-[15px] text-[var(--levo-primary)]">
                    {!! \App\Models\Product::getProductPrice($product, $store) !!}
                    </span>
                @else
                <span class="font-bold text-sm text-[var(--levo-primary)]">
                    <ins class="no-underline">{{ __('In Variant') }}</ins>
                </span>
                @endif
            </div>
            <div class="mt-2">
                <button class="addtocart-btn btn addcart-btn-globaly w-full bg-[var(--primary-color)] text-white text-sm font-semibold px-3 py-2 rounded-lg hover:bg-[var(--levo-primary-dark)] transition-colors"
                tabindex="0"
                product_id="{{ $product->id }}"
                variant_id="0"
                qty="1">
                <span>{{ __('Add to cart') }}</span>
                </button>
                {!! \App\Models\Product::ProductcardButton($store->slug, $product) !!}
            </div>
        </div>
</div>