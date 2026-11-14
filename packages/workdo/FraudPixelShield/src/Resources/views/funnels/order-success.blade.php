<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>অর্ডার নিশ্চিত হয়েছে - {{ $store->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'jakarta': ['"Plus Jakarta Sans"', 'sans-serif'],
                        'hind': ['"Hind Siliguri"', 'sans-serif'],
                    },
                    colors: {
                        'royal': {
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --theme-primary: {{ $funnel->theme_color ?? '#4f46e5' }};
        }
        .font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-hind { font-family: 'Hind Siliguri', sans-serif; }
    </style>
    
    {{-- Meta Pixel - OrderReceivedHold Event (NOT Purchase - per Pixel Isolation) --}}
    @php
        $pixelId = $store->meta_pixel_id ?? '';
        $capiEnabled = $store->capi_enabled ?? false;
    @endphp
    
    @if($pixelId)
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        
        fbq('init', '{{ $pixelId }}');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"/></noscript>
    @endif
</head>
<body class="font-hind bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4" dir="rtl">

<div class="max-w-md w-full">
    <div class="bg-white rounded-2xl shadow-xl p-8 sm:p-10 text-center">
        {{-- Success Animation --}}
        <div class="mb-8">
            <div class="w-24 h-24 mx-auto mb-6 relative">
                <div class="absolute inset-0 rounded-full bg-green-100 animate-ping"></div>
                <div class="absolute inset-0 rounded-full bg-green-200 animate-ping" style="animation-delay: 0.5s;"></div>
                <div class="relative w-full h-full rounded-full bg-green-500 flex items-center justify-center">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
        </div>

        <h1 class="font-jakarta font-extrabold text-3xl text-gray-900 mb-3">অর্ডার নিশ্চিত হয়েছে!</h1>
        <p class="text-gray-600 mb-6">আপনার অর্ডার সফলভাবে জমা হয়েছে আমাদের টিম শীঘ্রই আপনার সাথে যোগাযোগ করবে</p>

        {{-- Order Details Card --}}
        <div class="bg-gray-50 rounded-xl p-5 mb-6 text-right">
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">অর্ডার আইডি</span>
                    <span class="font-jakarta font-bold text-gray-900">#{{ $order->order_number ?? $order->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">প্রোডাক্ট</span>
                    <span class="font-medium text-gray-900 truncate pr-4 max-w-[60%]">{{ $order->product_name ?? $funnel->product->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">মোট টাকা</span>
                    <span class="font-jakarta font-bold text-xl" style="color: var(--theme-primary);">{{ \Modules\Ecommerce\Entities\Currency::formatPrice($order->grand_total ?? $funnel->product->price ?? 0) }}</span>
                </div>
                <div class="flex justify-between pt-3 border-t border-gray-200">
                    <span class="text-gray-500">পেমেন্ট মেথড</span>
                    <span class="font-medium text-gray-900">{{ $order->payment_method === 'cod' ? 'ক্যাশ অন ডেলিভারি (COD)' : 'অনলাইন (bKash/নগদ/রকেট)' }}</span>
                </div>
            </div>
        </div>

        {{-- Next Steps --}}
        <div class="mb-8 p-4 rounded-xl bg-blue-50 border border-blue-100 text-right">
            <h3 class="font-jakarta font-bold text-blue-900 mb-2 flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                এরপর কী হবে?
            </h3>
            <ul class="text-sm text-blue-800 space-y-1 text-right">
                <li class="flex items-center gap-2 justify-end"><svg class="w-4 h-4 flex-shrink-0 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> আপনার ফোনে কল বা SMS আসবে</li>
                <li class="flex items-center gap-2 justify-end"><svg class="w-4 h-4 flex-shrink-0 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> কুরিয়ার দিয়ে প্রোডাক্ট পাঠানো হবে</li>
                <li class="flex items-center gap-2 justify-end"><svg class="w-4 h-4 flex-shrink-0 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> প্রোডাক্ট হাতে পাওয়ার পর টাকা দিন (COD)</li>
            </ul>
        </div>

        {{-- Actions --}}
        <div class="space-y-3">
            <a href="{{ route('funnel.landing', ['storeSlug' => $store->slug, 'slug' => $funnel->slug]) }}" 
               class="btn-primary-theme w-full py-3.5 rounded-xl font-jakarta font-semibold text-lg block transition-all">
                <svg class="w-5 h-5 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                ফিরে যান অফার পেজে
            </a>
            
            <a href="https://wa.me/8801XXXXXXXXX?text={{ urlencode('হ্যালো, আমি অর্ডার #' . ($order->order_number ?? $order->id) . ' নিশ্চিত করেছি কটি সময়ে ডেলিভারি হবে?') }}" 
               target="_blank"
               class="w-full py-3.5 rounded-xl font-jakarta font-semibold text-lg block text-center bg-green-600 text-white hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.611-.916-2.207-.247-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.572-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378 9.86 9.86 0 01-.475-.375 33.547 33.547 0 00-.31-.536c-2.643-2.537-2.737-5.449-.996-5.526 1.425-.066 4.542 1.773 4.656 2.347.113.567.113 1.264 0 1.794-.171.746-.627 1.275-1.1 1.67l-.004.005zm4.784-21.809c-4.811 0-8.716 3.905-8.716 8.716 0 2.491 1.114 4.728 2.872 6.248 1.412 1.242 2.828 2.83 3.021 3.021.13.018.26.018.389 0 .173-.018 1.797-1.957 3.229-3.359 1.768-1.74 3.245-4.08 3.245-6.565C21.239 7.101 17.334 3.196 12.524 3.196z"/></svg>
                WhatsApp-এ যোগাযোগ
            </a>
        </div>

        <p class="mt-6 text-xs text-gray-400">© {{ date('Y') }} {{ $store->name }} | Powered by Ecom Sentrix</p>
    </div>
</div>

{{-- Meta Pixel Custom Event: OrderReceivedHold (NOT Purchase - per Pixel Isolation) --}}
@if($pixelId && $order)
<script>
    (function() {
        var orderData = {
            order_id: '{{ $order->order_number ?? $order->id }}',
            product_name: '{{ $order->product_name ?? $funnel->product->name ?? '' }}',
            value: {{ $order->grand_total ?? $funnel->product->price ?? 0 }},
            currency: 'BDT',
            pixel_status: 'hold',
            payment_method: '{{ $order->payment_method ?? 'cod' }}'
        };
        
        // Fire custom event for held orders (NOT Purchase)
        if (typeof fbq !== 'undefined') {
            fbq('trackCustom', 'OrderReceivedHold', orderData);
            console.log('[Meta Pixel] OrderReceivedHold fired:', orderData);
        }
        
        // Also push to dataLayer for GTM
        if (typeof dataLayer !== 'undefined') {
            dataLayer.push({
                event: 'OrderReceivedHold',
                ecommerce: {
                    transaction_id: orderData.order_id,
                    value: orderData.value,
                    currency: orderData.currency,
                    items: [{
                        item_id: orderData.order_id,
                        item_name: orderData.product_name,
                        price: orderData.value,
                        quantity: 1
                    }]
                },
                pixel_status: 'hold'
            });
            console.log('[GTM] OrderReceivedHold pushed to dataLayer');
        }
    })();
</script>
@endif

</body>
</html>