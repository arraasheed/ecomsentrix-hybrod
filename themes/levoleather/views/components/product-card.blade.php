@props(['product', 'store'])

{{-- Demo Product Card 3 replica: discount tag, price+strike, 2 buttons (অর্ডার করুন / যোগ করুন), compact like reference --}}
<div class="flex product-card flex-col h-full bg-white rounded-xl border overflow-hidden transition-shadow hover:shadow-md" style="border-color:#f0e4d8;">
        {!! \App\Models\Product::productSalesPage( $store->slug, $product->id, false) !!}
        <div class="relative">
            <a href="{{ url($store->slug . '/product/' . $product->slug) }}" class="block">
                <div class="relative pt-[100%] overflow-hidden bg-[#f6efe7]">
                    <img src="{{ get_file($product->cover_image_path)}}"
                    width="400" height="400"
                    alt="{{ $product->name }}"
                    loading="lazy"
                    class="absolute inset-0 w-full h-full object-contain p-2 product-image" />
                </div>
            </a>
            {{-- Discount tag (demo: green -34% OFF) --}}
            @php $hasDiscount = !empty($product->sale_price) && $product->sale_price < $product->price && $product->price > 0; $discountPct = $hasDiscount ? round((1 - $product->sale_price/$product->price)*100) : 0; @endphp
            @if($hasDiscount)
                <span class="absolute top-2 left-2 bg-[#00c153] text-white text-[11px] font-bold px-2 py-1 rounded-md">-{{ $discountPct }}% OFF</span>
            @endif
            <div class="absolute top-2 right-2">
                <button class="wishlist-btn wishbtn-globaly {{ $product->in_whishlist ? 'active' : ''}} flex bg-white/95 rounded-full p-1.5 shadow-sm border border-black/5 hover:bg-white" 
                        product_id="{{ $product->id }}"
                        in_wishlist="{{ $product->in_whishlist ? 'remove' : 'add' }}" aria-label="Wishlist">
                        <i class="far fa-heart text-gray-700 text-xs"></i>
                    </button>
                    {!! \App\Models\Product::actionLinks( $store, $product) !!}
                </div>
            </div>
        <div class="flex flex-col flex-1 p-2.5 gap-1.5">
            <h3 class="font-medium text-[13px] leading-tight line-clamp-2 min-h-[2.4em]">
                <a href="{{ url($store->slug . '/product/' . $product->slug) }}" class="hover:text-[var(--levo-primary)] transition-colors">{{ $product->name }}</a>
            </h3>
            <div class="flex items-baseline gap-1.5">
                @if ($product->variant_product == 0)
                    <span class="font-bold text-[13px]" style="color: var(--levo-primary,#e8007c);">
                    {!! \App\Models\Product::getProductPrice($product, $store) !!}
                    </span>
                @else
                <span class="font-bold text-xs" style="color: var(--levo-primary,#e8007c);">
                    <ins class="no-underline">{{ __('In Variant') }}</ins>
                </span>
                @endif
            </div>
            {{-- Demo 2 buttons: অর্ডার করুন (primary) + যোগ করুন (outline) --}}
            <div class="mt-1 flex flex-col gap-1.5">
                <button class="addtocart-btn btn addcart-btn-globaly w-full text-white text-xs font-semibold px-2 py-1.5 rounded-md hover:opacity-90 transition" style="background: var(--levo-primary,#e8007c);"
                tabindex="0" product_id="{{ $product->id }}" variant_id="0" qty="1">
                <span>{{ __('অর্ডার করুন') }}</span>
                </button>
                <button class="w-full text-xs font-semibold px-2 py-1.5 rounded-md border hover:bg-[#faf5ef] transition addcart-btn-globaly" style="color: var(--levo-primary,#e8007c); border-color: var(--levo-primary,#e8007c);"
                tabindex="0" product_id="{{ $product->id }}" variant_id="0" qty="1">
                <span>{{ __('যোগ করুন') }}</span>
                </button>
                {!! \App\Models\Product::ProductcardButton($store->slug, $product) !!}
            </div>
        </div>
</div>