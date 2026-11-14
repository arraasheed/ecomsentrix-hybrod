<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $funnel->title }} - {{ $store->name }}</title>
    <meta name="description" content="{{ $funnel->description ?? $funnel->title }}">
    <meta property="og:title" content="{{ $funnel->title }}">
    <meta property="og:description" content="{{ $funnel->description ?? $funnel->title }}">
    <meta property="og:type" content="website">
    @if($funnel->review_gallery)
        @php $images = json_decode($funnel->review_gallery, true); @endphp
        @if(!empty($images))
            <meta property="og:image" content="{{ asset('storage/' . $images[0]) }}">
        @endif
    @endif

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Tailwind CDN for landing page (lightweight) --}}
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
                            50: '#f0efff',
                            100: '#e0defe',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    
    {{-- Meta Pixel & GTM --}}
    @php
        $pixelId = $store->meta_pixel_id ?? '';
        $gtmId = $store->gtm_id ?? '';
        $product = $funnel->product;
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
    
    @if($gtmId)
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ $gtmId }}');</script>
    @endif

    {{-- Styles --}}
    <style>
        :root {
            --theme-primary: {{ $funnel->theme_color ?? '#4f46e5' }};
            --theme-primary-rgb: {{ implode(', ', array_map('hexdec', str_split(ltrim($funnel->theme_color ?? '#4f46e5', '#'), 2))) }};
        }
        
        .font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-hind { font-family: 'Hind Siliguri', sans-serif; }
        
        .btn-primary-theme {
            background: linear-gradient(135deg, var(--theme-primary), color-mix(in srgb, var(--theme-primary) 70%, black));
            color: white;
            transition: all 0.2s ease;
        }
        .btn-primary-theme:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px color-mix(in srgb, var(--theme-primary) 40%, transparent);
        }
        .btn-primary-theme:active { transform: translateY(0); }
        
        .countdown-digit {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #f8fafc;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            min-width: 60px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.1);
        }
        
        .review-carousel-item {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        
        .checkout-sticky {
            position: sticky;
            bottom: 0;
            z-index: 50;
        }
        
        @media (min-width: 768px) {
            .checkout-sticky {
                position: relative;
                bottom: auto;
            }
        }
        
        .input-field:focus {
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--theme-primary) 30%, transparent);
            border-color: var(--theme-primary);
        }
        
        .video-wrapper {
            aspect-ratio: 16/9;
            border-radius: 16px;
            overflow: hidden;
            background: #0f172a;
        }
        
        .trust-badge {
            animation: pulse-glow 3s ease-in-out infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--theme-primary) 40%, transparent); }
            50% { box-shadow: 0 0 20px 4px color-mix(in srgb, var(--theme-primary) 30%, transparent); }
        }
        
        .countdown-ended { display: none; }
        .countdown-active.countdown-ended { display: block; }
        .countdown-active:not(.countdown-ended) .countdown-ended { display: none; }
        
        /* Safe area for mobile */
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 0); }
    </style>
</head>
<body class="font-hind bg-gray-50 min-h-screen" dir="rtl">
    
    {{-- Meta Pixel ViewContent Event (Page Load) --}}
    @if($pixelId && $product)
    <script>
        (function() {
            var productData = {
                content_type: 'product',
                content_ids: ['{{ $product->id }}'],
                content_name: '{{ addslashes($product->name) }}',
                value: {{ $product->price ?? 0 }},
                currency: 'BDT',
            };
            
            // Fire ViewContent when fbq is ready
            if (typeof fbq !== 'undefined') {
                fbq('track', 'ViewContent', productData);
                console.log('[Meta Pixel] ViewContent fired:', productData);
            }
            
            // Also push to dataLayer for GTM
            if (typeof dataLayer !== 'undefined') {
                dataLayer.push({
                    event: 'view_item',
                    ecommerce: {
                        items: [{
                            item_id: '{{ $product->id }}',
                            item_name: '{{ addslashes($product->name) }}',
                            price: {{ $product->price ?? 0 }},
                            quantity: 1,
                            currency: 'BDT'
                        }]
                    }
                });
                console.log('[GTM] view_item pushed to dataLayer');
            }
        })();
    </script>
    @endif

{{-- Hero Section --}}
<section class="relative overflow-hidden" style="background: linear-gradient(180deg, color-mix(in srgb, var(--theme-primary) 8%, transparent) 0%, #ffffff 100%);">
    <div class="absolute inset-0 opacity-10" style="background: radial-gradient(ellipse at center, var(--theme-primary) 0%, transparent 70%);"></div>
    
    <div class="relative max-w-4xl mx-auto px-4 py-8 sm:py-12 lg:py-16">
        @if($funnel->countdown_end && \Carbon\Carbon::parse($funnel->countdown_end)->isFuture())
        <div class="mb-6 text-center" id="countdownBanner" role="status" aria-live="polite">
            <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full bg-white/80 backdrop-blur-sm shadow-lg border border-gray-200">
                <span class="font-jakarta font-semibold text-sm text-gray-700">⏰ অফার শেষ হওয়ার সময়:</span>
                <div class="flex items-center gap-1" id="countdownTimer">
                    <div class="countdown-digit font-jakarta font-bold text-lg" id="days">00</div>
                    <span class="text-gray-500 font-bold">:</span>
                    <div class="countdown-digit font-jakarta font-bold text-lg" id="hours">00</div>
                    <span class="text-gray-500 font-bold">:</span>
                    <div class="countdown-digit font-jakarta font-bold text-lg" id="minutes">00</div>
                    <span class="text-gray-500 font-bold">:</span>
                    <div class="countdown-digit font-jakarta font-bold text-lg" id="seconds">00</div>
                </div>
            </div>
        </div>
        @endif

        <div class="text-center mb-8">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium" style="background: color-mix(in srgb, var(--theme-primary) 15%, white); color: var(--theme-primary); border: 1px solid color-mix(in srgb, var(--theme-primary) 30%, transparent);">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-current opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-current"></span>
                </span>
                লিমিটেড টাইম অফার
            </span>
        </div>

        <h1 class="font-jakarta font-extrabold text-3xl sm:text-4xl lg:text-5xl text-gray-900 mb-4 leading-tight">
            {{ $funnel->title }}
        </h1>
        
        @if($funnel->description)
        <p class="text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto mb-8 leading-relaxed">
            {{ $funnel->description }}
        </p>
        @endif

        @if($funnel->video_url)
        <div class="mb-8 video-wrapper shadow-2xl">
            @php
                $videoUrl = $funnel->video_url;
                $embedUrl = '';
                if (str_contains($videoUrl, 'youtube.com') || str_contains($videoUrl, 'youtu.be')) {
                    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $videoUrl, $matches);
                    if (!empty($matches[1])) { $embedUrl = 'https://www.youtube.com/embed/' . $matches[1] . '?rel=0&modestbranding=1'; }
                } elseif (str_contains($videoUrl, 'vimeo.com')) {
                    preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $matches);
                    if (!empty($matches[1])) { $embedUrl = 'https://player.vimeo.com/video/' . $matches[1]; }
                } elseif (str_contains($videoUrl, 'facebook.com') || str_contains($videoUrl, 'fb.watch')) {
                    $embedUrl = 'https://www.facebook.com/plugins/video.php?href=' . urlencode($videoUrl) . '&show_text=0&width=560';
                }
            @endphp
            @if($embedUrl)
                <iframe src="{{ $embedUrl }}" 
                        title="{{ $funnel->title }}" 
                        class="w-full h-full" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen></iframe>
            @else
                <div class="w-full h-full flex items-center justify-center text-gray-500">ভিডিও লোড করা যাচ্ছে না</div>
            @endif
        </div>
        @endif
    </div>
</section>

{{-- Product Showcase & Checkout Section --}}
<main class="max-w-4xl mx-auto px-4 pb-8 sm:pb-12 lg:pb-16">
    <div class="grid lg:grid-cols-12 gap-8">
        
        {{-- Left Column: Product Gallery & Info --}}
        <div class="lg:col-span-7 space-y-6">
            
            {{-- Product Image Gallery --}}
            @if($funnel->product)
            <div class="rounded-2xl overflow-hidden shadow-xl bg-white">
                <div class="aspect-[4/3] relative overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                    @if($funnel->product->image)
                        <img src="{{ asset('storage/' . $funnel->product->image) }}" 
                             alt="{{ $funnel->product->name }}" 
                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                             loading="eager">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400">ছবি নেই</div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Review Carousel --}}
            @if($funnel->review_gallery)
                @php $reviewImages = json_decode($funnel->review_gallery, true); @endphp
                @if(!empty($reviewImages))
                <section class="bg-white rounded-2xl p-6 shadow-lg" aria-labelledby="reviews-heading">
                    <h2 id="reviews-heading" class="font-jakarta font-bold text-xl text-gray-900 mb-5 flex items-center gap-2">
                        <svg class="w-6 h-6" style="color: var(--theme-primary);" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        কাস্টমার রিভিউ
                    </h2>
                    
                    <div class="relative">
                        <div id="reviewCarousel" class="overflow-x-auto snap-x snap-mandatory flex gap-4 pb-4 -mx-4 px-4 scrollbar-hide" role="region" aria-label="রিভিউ কারোসেল">
                            @foreach($reviewImages as $index => $img)
                            <div class="review-carousel-item flex-shrink-0 w-[280px] sm:w-[320px] snap-center">
                                <div class="bg-gray-50 rounded-xl p-4 h-full shadow-sm hover:shadow-md transition-shadow">
                                    <img src="{{ asset('storage/' . $img) }}" alt="রিভিউ {{ $index + 1 }}" class="w-full h-48 object-cover rounded-lg mb-3">
                                    <div class="flex items-center gap-1 text-amber-500">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        {{-- Navigation arrows --}}
                        <button type="button" id="reviewPrev" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-3 sm:-translate-x-4 w-10 h-10 rounded-full bg-white shadow-lg flex items-center justify-center text-gray-700 hover:bg-gray-100 transition-colors hidden sm:flex" aria-label="পূর্বের রিভিউ">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" id="reviewNext" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-3 sm:translate-x-4 w-10 h-10 rounded-full bg-white shadow-lg flex items-center justify-center text-gray-700 hover:bg-gray-100 transition-colors hidden sm:flex" aria-label="পরবর্তী রিভিউ">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </section>
                @endif
            @endif

            {{-- Product Details --}}
            @if($funnel->product)
            <section class="bg-white rounded-2xl p-6 shadow-lg" aria-labelledby="details-heading">
                <h2 id="details-heading" class="font-jakarta font-bold text-xl text-gray-900 mb-5">প্রোডাক্ট বিবরণ</h2>
                
                <div class="space-y-4">
                    <div class="flex items-baseline gap-3">
                        <span class="font-jakarta font-extrabold text-3xl" style="color: var(--theme-primary);">
                            {{ \Modules\Ecommerce\Entities\Currency::formatPrice($funnel->product->price) }}
                        </span>
                        @if($funnel->product->compare_price && $funnel->product->compare_price > $funnel->product->price)
                            <span class="text-xl text-gray-400 line-through">
                                {{ \Modules\Ecommerce\Entities\Currency::formatPrice($funnel->product->compare_price) }}
                            </span>
                            <span class="font-bold text-green-600 bg-green-50 px-3 py-1 rounded-full text-sm">
                                {{ round((($funnel->product->compare_price - $funnel->product->price) / $funnel->product->compare_price) * 100) }}% ছাড়
                            </span>
                        @endif
                    </div>
                    
                    <div class="pt-4 border-t border-gray-100">
                        <h3 class="font-semibold text-gray-900 mb-3">প্রধান ফিচারস</h3>
                        <ul class="space-y-2 text-gray-600">
                            <li class="flex items-center gap-2"><svg class="w-5 h-5 flex-shrink-0" style="color: var(--theme-primary);" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> ক্যাশ অন ডেলিভারি (COD) উপলব্ধ</li>
                            <li class="flex items-center gap-2"><svg class="w-5 h-5 flex-shrink-0" style="color: var(--theme-primary);" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> ফรี হোম ডেলিভারি (শর্ত সাপেক্ষে)</li>
                            <li class="flex items-center gap-2"><svg class="w-5 h-5 flex-shrink-0" style="color: var(--theme-primary);" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> ৭ দিন রিটার্ন পলিসি</li>
                            <li class="flex items-center gap-2"><svg class="w-5 h-5 flex-shrink-0" style="color: var(--theme-primary);" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> অরিজিনাল প্রোডাক্ট গ্যারান্টি</li>
                        </ul>
                    </div>
                </div>
            </section>
            @endif
        </div>

        {{-- Right Column: Sticky Checkout Form --}}
        <div class="lg:col-span-5">
            <div class="checkout-sticky">
                <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-24" style="max-height: calc(100vh - 6rem); overflow-y: auto;" aria-labelledby="checkout-heading">
                    <h2 id="checkout-heading" class="font-jakarta font-bold text-xl text-gray-900 mb-5 flex items-center gap-2">
                        <svg class="w-6 h-6" style="color: var(--theme-primary);" fill="currentColor" viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99-.9-1.99-2S5.9 14 7 14s2 .9 2 2-.9 2-2 2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm13.5-2h-3v-2h3V4h-3V2h3c.55 0 1 .45 1 1v2h3v2h-3v2h3v2z"/></svg>
                        অর্ডার নিশ্চিত করুন
                    </h2>

                    @if($funnel->countdown_end && \Carbon\Carbon::parse($funnel->countdown_end)->isPast())
                    <div class="mb-5 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm font-medium text-center">
                        ⚠️ এই অফারের মেয়াদ শেষ হয়ে গেছে নতুন দাম প্রযোজ্য হতে পারে
                    </div>
                    @endif

                    <form id="checkoutForm" method="POST" action="{{ route('funnel.order.submit', ['storeSlug' => $store->slug]) }}" class="space-y-5" novalidate>
                        @csrf
                        <input type="hidden" name="funnel_id" value="{{ $funnel->id }}">
                        <input type="hidden" name="product_id" value="{{ $funnel->product_id }}">
                        <input type="hidden" name="funnel_slug" value="{{ $funnel->slug }}">
                        <input type="hidden" name="source" value="landing_page">

                        {{-- Phone Field with Live Fraud Check --}}
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">ফোন নম্বর <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="tel" name="phone" id="phone" class="input-field w-full px-4 py-3.5 rounded-xl border border-gray-300 bg-white text-lg font-mono placeholder-gray-400 transition-all" 
                                       placeholder="০১XXXXXXXXX" maxlength="11" pattern="^01[3-9]\d{8}$" required autocomplete="tel"
                                       aria-describedby="phoneHelp">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <span class="font-mono text-lg">+৮৮</span>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center" id="phoneStatus" aria-live="polite"></div>
                            </div>
                            <p id="phoneHelp" class="mt-1.5 text-sm text-gray-500">১১ ডিজিটের বাংলাদেশি মোবাইল নম্বর দিন নম্বর টাইপ করার সাথে সাথে ফ্রড চেক শুরু হবে</p>
                            <div id="fraudCheckResult" class="mt-2 hidden" role="alert"></div>
                        </div>

                        {{-- Name --}}
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">পূর্ণ নাম <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" class="input-field w-full px-4 py-3.5 rounded-xl border border-gray-300 bg-white text-lg placeholder-gray-400 transition-all" 
                                   placeholder="আপনার পূর্ণ নাম" required autocomplete="name">
                        </div>

                        {{-- Address --}}
                        <div>
                            <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">ঠিকানা <span class="text-red-500">*</span></label>
                            <textarea name="address" id="address" rows="2" class="input-field w-full px-4 py-3.5 rounded-xl border border-gray-300 bg-white text-lg placeholder-gray-400 transition-all resize-none" 
                                      placeholder="বillas নম্বর, রোড, এলাকা, জেলা" required autocomplete="street-address"></textarea>
                        </div>

                        {{-- City/District Dropdown --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">জেলা <span class="text-red-500">*</span></label>
                                <select name="city" id="city" class="input-field w-full px-4 py-3.5 rounded-xl border border-gray-300 bg-white text-lg transition-all" required>
                                    <option value="">জেলা নির্বাচন করুন</option>
                                    @foreach(['Dhaka','Chittagong','Rajshahi','Khulna','Sylhet','Barisal','Rangpur','Mymensingh','Comilla','Narayanganj','Gazipur','Bogra','Jessore','Dinajpur','Faridpur','Pabna','Tangail','Kushtia','Noakhali','Cox\'s Bazar'] as $district)
                                        <option value="{{ $district }}">{{ $district }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="area" class="block text-sm font-semibold text-gray-700 mb-2">থানা/উপজেলা <span class="text-red-500">*</span></label>
                                <input type="text" name="area" id="area" class="input-field w-full px-4 py-3.5 rounded-xl border border-gray-300 bg-white text-lg placeholder-gray-400 transition-all" placeholder="থানা/উপজেলা" required autocomplete="address-level2">
                            </div>
                        </div>

                        {{-- Payment Method --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">পেমেন্ট মেথড <span class="text-red-500">*</span></label>
                            <div class="space-y-2" role="radiogroup" aria-label="পেমেন্ট মেথড">
                                <label class="relative flex items-center p-3 rounded-xl border-2 border-gray-200 cursor-pointer hover:border-gray-300 transition-all has-[:checked]:border-primary-theme has-[:checked]:bg-primary-50">
                                    <input type="radio" name="payment_method" value="cod" class="sr-only" checked required>
                                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full relative mr-3 flex-shrink-0 has-[:checked]:border-primary-theme has-[:checked]:after:content-[''] has-[:checked]:after:absolute has-[:checked]:after:top-1/2 has-[:checked]:after:left-1/2 has-[:checked]:after:-translate-x-1/2 has-[:checked]:after:-translate-y-1/2 has-[:checked]:after:w-2 has-[:checked]:after:h-2 has-[:checked]:after:rounded-full" style="background-color: var(--theme-primary);"></div>
                                    <div>
                                        <span class="font-medium text-gray-900">ক্যাশ অন ডেলিভারি (COD)</span>
                                        <p class="text-xs text-gray-500 mt-0.5">প্রোডাক্ট পাওয়ার সময় টাকা দিন</p>
                                    </div>
                                </label>
                                <label class="relative flex items-center p-3 rounded-xl border-2 border-gray-200 cursor-pointer hover:border-gray-300 transition-all has-[:checked]:border-primary-theme has-[:checked]:bg-primary-50">
                                    <input type="radio" name="payment_method" value="bkash" class="sr-only">
                                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full relative mr-3 flex-shrink-0 has-[:checked]:border-primary-theme has-[:checked]:after:content-[''] has-[:checked]:after:absolute has-[:checked]:after:top-1/2 has-[:checked]:after:left-1/2 has-[:checked]:after:-translate-x-1/2 has-[:checked]:after:-translate-y-1/2 has-[:checked]:after:w-2 has-[:checked]:after:h-2 has-[:checked]:after:rounded-full" style="background-color: var(--theme-primary);"></div>
                                    <div>
                                        <span class="font-medium text-gray-900 flex items-center gap-1"><span class="text-red-600 font-bold">bKash</span> / নগদ / রকেট</span>
                                        <p class="text-xs text-gray-500 mt-0.5">অনলাইন পেমেন্ট (সুবিধা শুল্ক প্রযোজ্য)</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Order Summary --}}
                        <div class="pt-4 border-t border-gray-100 space-y-2">
                            <div class="flex justify-between text-gray-600">
                                <span>প্রোডাক্ট দাম</span>
                                <span class="font-medium" id="summaryPrice">{{ \Modules\Ecommerce\Entities\Currency::formatPrice($funnel->product->price ?? 0) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>ডেলিভারি চার্জ</span>
                                <span class="font-medium" id="deliveryCharge">গণনা হচ্ছে...</span>
                            </div>
                            <div class="flex justify-between text-gray-600 border-t border-gray-100 pt-2">
                                <span class="font-semibold text-lg">মোট দেয়</span>
                                <span class="font-extrabold text-lg" style="color: var(--theme-primary);" id="totalAmount">{{ \Modules\Ecommerce\Entities\Currency::formatPrice($funnel->product->price ?? 0) }}</span>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" id="submitBtn" class="btn-primary-theme w-full py-4 rounded-xl font-jakarta font-semibold text-lg flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all" disabled>
                            <svg class="w-6 h-6 animate-spin hidden" id="btnSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span id="btnText">ফোন নম্বর যাচাই করুন</span>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </button>
                    </form>

                    {{-- Trust Badges --}}
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <p class="text-xs text-center text-gray-500 mb-3 uppercase tracking-wider font-medium">বিশ্বস্ততা ও নিরাপত্তা</p>
                        <div class="flex flex-wrap justify-center gap-3">
                            <div class="trust-badge px-3 py-1.5 rounded-full text-xs font-medium bg-white border" style="border-color: color-mix(in srgb, var(--theme-primary) 30%, transparent); color: var(--theme-primary);">
                                <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                সিকিউর চেকআউট
                            </div>
                            <div class="trust-badge px-3 py-1.5 rounded-full text-xs font-medium bg-white border" style="border-color: color-mix(in srgb, var(--theme-primary) 30%, transparent); color: var(--theme-primary);">
                                <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                ৭ দিন রিটার্ন
                            </div>
                            <div class="trust-badge px-3 py-1.5 rounded-full text-xs font-medium bg-white border" style="border-color: color-mix(in srgb, var(--theme-primary) 30%, transparent); color: var(--theme-primary);">
                                <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                অরিজিনাল গ্যারান্টি
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

{{-- Footer --}}
<footer class="bg-gray-900 text-gray-400 py-8 px-4">
    <div class="max-w-4xl mx-auto text-center">
        <p class="font-medium text-white mb-2">{{ $store->name }}</p>
        <p class="text-sm">© {{ date('Y') }} সকল অধিকার সংরক্ষিত</p>
        <p class="text-xs mt-2">Powered by <span class="font-semibold" style="color: var(--theme-primary);">Ecom Sentrix</span></p>
    </div>
</footer>

{{-- Scripts --}}
<script>
    // Global config
    const FUNNEL_CONFIG = {
        storeSlug: '{{ $store->slug }}',
        funnelId: '{{ $funnel->id }}',
        productId: '{{ $funnel->product_id }}',
        productPrice: {{ $funnel->product->price ?? 0 }},
        fraudCheckUrl: '{{ route('fraud.live.check', ['slug' => $store->slug]) }}',
        fraudLogUrl: '{{ route('fraud.log.lead', ['slug' => $store->slug]) }}',
        orderSubmitUrl: '{{ route('funnel.order.submit', ['storeSlug' => $store->slug]) }}',
        countdownEnd: {{ $funnel->countdown_end ? '"' . \Carbon\Carbon::parse($funnel->countdown_end)->toISOString() . '"' : 'null' }},
    };

    // Phone validation & formatting
    const phoneInput = document.getElementById('phone');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');
    const phoneStatus = document.getElementById('phoneStatus');
    const fraudCheckResult = document.getElementById('fraudCheckResult');
    const deliveryChargeEl = document.getElementById('deliveryCharge');
    const totalAmountEl = document.getElementById('totalAmount');
    
    let fraudCheckDebounce = null;
    let fraudChecked = false;
    let fraudPassed = false;
    let deliveryCharge = 0;
    let initiateCheckoutFired = false;

    // Format phone input (only numbers, max 11 digits)
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        e.target.value = value;
        
        // Fire Meta Pixel InitiateCheckout on first input
        if (!initiateCheckoutFired && value.length > 0) {
            initiateCheckoutFired = true;
            fireInitiateCheckout(value);
        }
        
        updatePhoneStatus(value);
        validateForm();
    });

    function fireInitiateCheckout(phone) {
        @if($pixelId && $product)
        if (typeof fbq !== 'undefined') {
            var checkoutData = {
                content_type: 'product',
                content_ids: ['{{ $product->id }}'],
                content_name: '{{ addslashes($product->name) }}',
                value: {{ $product->price ?? 0 }},
                currency: 'BDT',
                num_items: 1,
            };
            fbq('track', 'InitiateCheckout', checkoutData);
            console.log('[Meta Pixel] InitiateCheckout fired:', checkoutData);
        }
        
        // Also push to dataLayer for GTM
        if (typeof dataLayer !== 'undefined') {
            dataLayer.push({
                event: 'begin_checkout',
                ecommerce: {
                    items: [{
                        item_id: '{{ $product->id }}',
                        item_name: '{{ addslashes($product->name) }}',
                        price: {{ $product->price ?? 0 }},
                        quantity: 1,
                        currency: 'BDT'
                    }]
                }
            });
            console.log('[GTM] begin_checkout pushed to dataLayer');
        }
        @endif
    }

    function firePurchaseEvent(orderId, totalAmount) {
        @if($pixelId)
        if (typeof fbq !== 'undefined') {
            var purchaseData = {
                content_type: 'product',
                content_ids: ['{{ $product->id }}'],
                content_name: '{{ addslashes($product->name) }}',
                value: totalAmount,
                currency: 'BDT',
                num_items: 1,
                order_id: orderId
            };
            fbq('track', 'Purchase', purchaseData);
            console.log('[Meta Pixel] Purchase fired:', purchaseData);
        }
        
        // Also push to dataLayer for GTM
        if (typeof dataLayer !== 'undefined') {
            dataLayer.push({
                event: 'purchase',
                ecommerce: {
                    transaction_id: orderId,
                    value: totalAmount,
                    currency: 'BDT',
                    items: [{
                        item_id: '{{ $product->id }}',
                        item_name: '{{ addslashes($product->name) }}',
                        price: totalAmount,
                        quantity: 1,
                        currency: 'BDT'
                    }]
                }
            });
            console.log('[GTM] purchase pushed to dataLayer');
        }
        @endif
    }

    function updatePhoneStatus(phone) {
        phoneStatus.innerHTML = '';
        fraudCheckResult.classList.add('hidden');
        
        if (phone.length === 11 && /^01[3-9]\d{8}$/.test(phone)) {
            phoneStatus.innerHTML = '<svg class="w-6 h-6 text-yellow-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            debouncedFraudCheck(phone);
        } else if (phone.length > 0) {
            phoneStatus.innerHTML = '<svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>';
        }
    }

    function debouncedFraudCheck(phone) {
        clearTimeout(fraudCheckDebounce);
        fraudCheckDebounce = setTimeout(() => {
            runFraudCheck(phone);
        }, 1500); // 1.5s debounce as per spec
    }

    async function runFraudCheck(phone) {
        phoneStatus.innerHTML = '<svg class="w-6 h-6 text-blue-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        
        try {
            const response = await fetch(FUNNEL_CONFIG.fraudCheckUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ phone: phone })
            });
            
            const data = await response.json();
            
            if (data.success && data.data) {
                const d = data.data;
                fraudPassed = !d.is_fraud && d.delivery_rate >= 50;
                fraudChecked = true;
                deliveryCharge = d.delivery_charge || 0;
                
                updateDeliveryDisplay(deliveryCharge);
                showFraudResult(d);
                validateForm();
            } else {
                // Fail-safe: allow order but mark for review
                fraudPassed = true; // Fail-safe buffer
                fraudChecked = true;
                fraudCheckResult.classList.remove('hidden');
                fraudCheckResult.innerHTML = '<div class="p-3 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-sm">ফ্রড চেক সার্ভার অস্থির আপনার অর্ডার নিশ্চিত করা যাবে, তবে এটি ম্যানুয়াল রিভিউয়ে যাবে</div>';
                validateForm();
            }
        } catch (error) {
            console.error('Fraud check error:', error);
            // Fail-safe buffer
            fraudPassed = true;
            fraudChecked = true;
            fraudCheckResult.classList.remove('hidden');
            fraudCheckResult.innerHTML = '<div class="p-3 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-sm">নেটওয়ার্ক সমস্যা আপনার অর্ডার নিশ্চিত করা যাবে, তবে এটি ম্যানুয়াল রিভিউয়ে যাবে</div>';
            validateForm();
        }
    }

    function showFraudResult(data) {
        fraudCheckResult.classList.remove('hidden');
        
        if (data.is_fraud || data.delivery_rate < 50) {
            // Soft lock - show WhatsApp redirect
            const whatsappMsg = encodeURIComponent(
                `হ্যালো, আমি ${document.getElementById('name').value || 'কাস্টমার'}\n` +
                `ফোন: ${phoneInput.value}\n` +
                `প্রোডাক্ট: {{ $funnel->product->name ?? $funnel->title }}\n` +
                `ঠিকানা: ${document.getElementById('address').value || 'দেওয়া হয়নি'}\n\n` +
                `আমি এই প্রোডাক্টটি কিনতে চাই দয়া করে যোগাযোগ করুন`
            );
            const whatsappUrl = `https://wa.me/8801XXXXXXXXX?text=${whatsappMsg}`; // Store WhatsApp number should come from settings
            
            fraudCheckResult.innerHTML = `
                <div class="p-4 rounded-xl bg-gradient-to-r from-red-50 to-amber-50 border border-red-200">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-red-900 mb-1">অর্ডার সরাসরি নিশ্চিত করা সম্ভব নয়</h4>
                            <p class="text-sm text-red-800 mb-3">কারিগরি কারণে ওয়েবসাইট থেকে সরাসরি অর্ডার প্রসেস করা যাচ্ছে না দয়া করে WhatsApp-এ যোগাযোগ করুন</p>
                            <a href="${whatsappUrl}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg font-medium text-sm hover:bg-green-700 transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.611-.916-2.207-.247-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.572-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378 9.86 9.86 0 01-.475-.375 33.547 33.547 0 00-.31-.536c-2.643-2.537-2.737-5.449-.996-5.526 1.425-.066 4.542 1.773 4.656 2.347.113.567.113 1.264 0 1.794-.171.746-.627 1.275-1.1 1.67l-.004.005zm4.784-21.809c-4.811 0-8.716 3.905-8.716 8.716 0 2.491 1.114 4.728 2.872 6.248 1.412 1.242 2.828 2.83 3.021 3.021.13.018.26.018.389 0 .173-.018 1.797-1.957 3.229-3.359 1.768-1.74 3.245-4.08 3.245-6.565C21.239 7.101 17.334 3.196 12.524 3.196z"/></svg>
                                WhatsApp-এ অর্ডার দিন
                            </a>
                        </div>
                    </div>
                </div>
            `;
            submitBtn.disabled = true;
            btnText.textContent = 'WhatsApp-এ যোগাযোগ করুন';
            return;
        }
        
        // Success - show delivery rate
        const rateColor = data.delivery_rate >= 80 ? 'green' : 'yellow';
        fraudCheckResult.innerHTML = `
            <div class="p-3 rounded-lg bg-${rateColor}-50 border border-${rateColor}-200 text-${rateColor}-800 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                ডেলিভারি সাফল্য রেট: ${data.delivery_rate}% (${data.total_orders} অর্ডার) - ${data.delivery_rate >= 80 ? 'নিরাপদ' : 'সতর্কতা'}
            </div>
        `;
    }

    function updateDeliveryDisplay(charge) {
        deliveryCharge = charge;
        deliveryChargeEl.textContent = charge > 0 ? `৳${charge.toLocaleString('bn-BD')}` : 'ফ্রি';
        updateTotal();
    }

    function updateTotal() {
        const total = FUNNEL_CONFIG.productPrice + deliveryCharge;
        totalAmountEl.textContent = `৳${total.toLocaleString('bn-BD')}`;
    }

    function validateForm() {
        const phone = phoneInput.value;
        const name = document.getElementById('name').value.trim();
        const address = document.getElementById('address').value.trim();
        const city = document.getElementById('city').value;
        const area = document.getElementById('area').value.trim();
        
        const phoneValid = phone.length === 11 && /^01[3-9]\d{8}$/.test(phone);
        const allFieldsValid = phoneValid && name && address && city && area;
        
        submitBtn.disabled = !(allFieldsValid && fraudChecked && fraudPassed);
        
        if (allFieldsValid && fraudChecked && fraudPassed) {
            btnText.textContent = 'অর্ডার নিশ্চিত করুন';
        } else if (!fraudChecked) {
            btnText.textContent = 'ফোন নম্বর যাচাই করুন';
        }
    }

    // Form validation on input
    ['name', 'address', 'city', 'area'].forEach(id => {
        document.getElementById(id).addEventListener('input', validateForm);
        document.getElementById(id).addEventListener('change', validateForm);
    });

    // Form submit
    document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (submitBtn.disabled) return;
        
        submitBtn.disabled = true;
        btnSpinner.classList.remove('hidden');
        btnText.textContent = 'প্রসেস হচ্ছে...';
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch(FUNNEL_CONFIG.orderSubmitUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Fire Meta Pixel Purchase event before redirect (Delayed CAPI pattern - browser pixel)
                firePurchaseEvent(data.order_id, data.order_total || {{ $product->price ?? 0 }});
                
                // Redirect to thank you page or show success
                window.location.href = data.redirect_url || `{{ route('funnel.order.success', ['storeSlug' => $store->slug, 'order' => ':order_id']) }}`.replace(':order_id', data.order_id);
            } else {
                throw new Error(data.message || 'অর্ডার সাবমিট ব্যর্থ হয়েছে');
            }
        } catch (error) {
            console.error('Order submit error:', error);
            alert('অর্ডার সাবমিট করতে সমস্যা হয়েছে: ' + error.message);
            submitBtn.disabled = false;
            btnSpinner.classList.add('hidden');
            btnText.textContent = 'অর্ডার নিশ্চিত করুন';
        }
    });

    // Countdown timer
    {{ if($funnel->countdown_end && \Carbon\Carbon::parse($funnel->countdown_end)->isFuture()) }}
    function updateCountdown() {
        const end = new Date(FUNNEL_CONFIG.countdownEnd).getTime();
        const now = new Date().getTime();
        const diff = end - now;
        
        if (diff <= 0) {
            document.getElementById('countdownBanner').classList.add('countdown-ended');
            document.getElementById('countdownTimer').innerHTML = '<span class="font-jakarta font-bold text-lg text-red-600 px-4">অফার শেষ</span>';
            return;
        }
        
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        document.getElementById('days').textContent = String(days).padStart(2, '0');
        document.getElementById('hours').textContent = String(hours).padStart(2, '0');
        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
    {{ endif }}

    // Review carousel
    const carousel = document.getElementById('reviewCarousel');
    const prevBtn = document.getElementById('reviewPrev');
    const nextBtn = document.getElementById('reviewNext');
    
    if (carousel && prevBtn && nextBtn) {
        const scrollAmount = 320; // Card width + gap
        
        prevBtn.addEventListener('click', () => {
            carousel.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
        
        nextBtn.addEventListener('click', () => {
            carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
        
        // Show/hide arrows based on scroll position
        carousel.addEventListener('scroll', () => {
            prevBtn.style.display = carousel.scrollLeft > 10 ? 'flex' : 'none';
            nextBtn.style.display = (carousel.scrollLeft + carousel.clientWidth) < carousel.scrollWidth - 10 ? 'flex' : 'none';
        });
        
        // Initial check
        setTimeout(() => {
            prevBtn.style.display = 'none';
            nextBtn.style.display = carousel.scrollWidth > carousel.clientWidth ? 'flex' : 'none';
        }, 100);
    }

    // Initialize total
    updateTotal();
</script>
</body>
</html>