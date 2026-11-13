@php
    $pixelId = \App\Models\Utility::GetValueByName('facebook_pixel_id', $store->id);
    $pixelIsolation = \App\Models\Utility::GetValueByName('pixel_isolation_enabled', $store->id) === 'on';
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $funnel['title'] }} - {{ $store->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Hind Siliguri', sans-serif; background-color: #f3f4f6; }
        .gradient-text { background: linear-gradient(90deg, #6366f1, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .btn-shimmer { position: relative; overflow: hidden; }
        .btn-shimmer::after { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent); transform: rotate(45deg); animation: shimmer 3s infinite; }
        @keyframes shimmer { 0% { transform: translateX(-100%) rotate(45deg); } 100% { transform: translateX(100%) rotate(45deg); } }
        .shake { animation: shake 0.5s infinite; }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-2px); } 75% { transform: translateX(2px); } }
    </style>

    @if($pixelId)
    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
    document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ $pixelId }}');
    fbq('track', 'PageView');
    fbq('track', 'ViewContent', { content_name: '{{ $funnel['title'] }}', content_ids: ['{{ $product->id }}'], content_type: 'product', value: {{ $funnel['sale_price'] }}, currency: 'BDT' });
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"/></noscript>
    @endif
</head>
<body class="antialiased text-gray-800">

    <!-- Header / Sticky Bar -->
    <div class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-b py-3 px-4 flex justify-between items-center shadow-sm">
        <div class="font-bold text-xl gradient-text">{{ $store->name }}</div>
        <a href="#order-form" class="bg-indigo-600 text-white px-5 py-2 rounded-full font-bold text-sm btn-shimmer">অর্ডার করুন</a>
    </div>

    <!-- Hero Section -->
    <main class="pt-20 pb-24">
        <div class="max-w-xl mx-auto px-4">
            
            <!-- Product Title -->
            <h1 class="text-2xl md:text-3xl font-bold text-center leading-tight mb-4">
                {{ $funnel['title'] }}
            </h1>

            <!-- Video Player -->
            <div class="relative aspect-video rounded-2xl overflow-hidden shadow-2xl mb-6 bg-black">
                <iframe class="absolute inset-0 w-full h-full" src="{{ $funnel['video_url'] }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>

            <!-- Price & Countdown -->
            <div class="bg-white rounded-2xl p-6 shadow-lg mb-6 border-t-4 border-indigo-500">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <p class="text-sm text-gray-500 line-through">৳{{ $funnel['price'] }}</p>
                        <p class="text-3xl font-bold text-indigo-600">৳{{ $funnel['sale_price'] }}</p>
                    </div>
                    <div class="bg-red-50 text-red-600 px-4 py-2 rounded-xl text-center border border-red-100">
                        <p class="text-xs font-bold uppercase tracking-wider">অফার শেষ হবে</p>
                        <p id="timer" class="text-lg font-mono font-bold leading-none mt-1">00:00:00</p>
                    </div>
                </div>
                <a href="#order-form" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center py-4 rounded-xl font-bold text-xl shadow-lg transition-all active:scale-95 btn-shimmer">
                    অর্ডার করতে এখানে ক্লিক করুন
                </a>
            </div>

            <!-- Image & Details -->
            <div class="space-y-6 mb-10 text-lg leading-relaxed">
                <img src="{{ $funnel['image'] }}" class="w-full rounded-2xl shadow-lg" alt="{{ $funnel['title'] }}">
                <div class="prose prose-indigo max-w-none">
                    {!! $funnel['description'] !!}
                </div>
            </div>

            <!-- Why Buy From Us? -->
            <div class="grid grid-cols-2 gap-4 mb-10">
                <div class="bg-indigo-50 p-4 rounded-2xl text-center">
                    <span class="block text-2xl mb-1">🚚</span>
                    <p class="text-sm font-bold">দ্রুত ডেলিভারি</p>
                </div>
                <div class="bg-indigo-50 p-4 rounded-2xl text-center">
                    <span class="block text-2xl mb-1">🤝</span>
                    <p class="text-sm font-bold">ক্যাশ অন ডেলিভারি</p>
                </div>
                <div class="bg-indigo-50 p-4 rounded-2xl text-center">
                    <span class="block text-2xl mb-1">✅</span>
                    <p class="text-sm font-bold">১০০% কোয়ালিটি</p>
                </div>
                <div class="bg-indigo-50 p-4 rounded-2xl text-center">
                    <span class="block text-2xl mb-1">📞</span>
                    <p class="text-sm font-bold">সার্বক্ষণিক সাপোর্ট</p>
                </div>
            </div>

            <!-- Order Form -->
            <div id="order-form" class="bg-white rounded-2xl shadow-2xl p-6 border-2 border-indigo-500 overflow-hidden relative">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                <h2 class="text-2xl font-bold mb-6 text-center">অর্ডার করতে নিচের ফর্মটি পূরণ করুন</h2>
                
                <form id="funnel-checkout-form" class="space-y-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    
                    <div>
                        <label class="block text-sm font-bold mb-1">আপনার নাম <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all" placeholder="আপনার নাম লিখুন">
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-1">মোবাইল নম্বর <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="tel" id="checkout-phone" name="phone" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all" placeholder="আপনার ১১ ডিজিটের মোবাইল নম্বর">
                            <div id="phone-checking-loader" class="hidden absolute right-3 top-3.5">
                                <svg class="animate-spin h-5 w-5 text-indigo-500" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-1">সম্পূর্ণ ঠিকানা <span class="text-red-500">*</span></label>
                        <textarea name="address" required rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all" placeholder="বাসা/রোড নম্বর এবং এলাকার নাম লিখুন"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-3">ডেলিভারি এরিয়া <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex items-center p-3 border-2 rounded-xl cursor-pointer hover:bg-indigo-50 transition-all border-indigo-500 bg-indigo-50" id="label-inside">
                                <input type="radio" name="shipping_method" value="inside_dhaka" checked class="hidden" onchange="updateTotal(70)">
                                <div class="text-sm">
                                    <p class="font-bold">ঢাকার ভেতরে</p>
                                    <p class="text-xs text-gray-500">ডেলিভারি চার্জ: ৳৭০</p>
                                </div>
                            </label>
                            <label class="relative flex items-center p-3 border-2 rounded-xl cursor-pointer hover:bg-indigo-50 transition-all border-gray-200" id="label-outside">
                                <input type="radio" name="shipping_method" value="outside_dhaka" class="hidden" onchange="updateTotal(130)">
                                <div class="text-sm">
                                    <p class="font-bold">ঢাকার বাইরে</p>
                                    <p class="text-xs text-gray-500">ডেলিভারি চার্জ: ৳১৩০</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                        <div class="flex justify-between mb-2">
                            <span>প্রোডাক্টের দাম:</span>
                            <span class="font-bold">৳{{ $funnel['sale_price'] }}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span>ডেলিভারি চার্জ:</span>
                            <span id="shipping-fee" class="font-bold">৳৭০</span>
                        </div>
                        <hr class="my-2">
                        <div class="flex justify-between text-lg font-bold text-indigo-600">
                            <span>সর্বমোট:</span>
                            <span id="grand-total">৳{{ $funnel['sale_price'] + 70 }}</span>
                        </div>
                    </div>

                    <button type="submit" id="submit-btn" class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-5 rounded-2xl font-bold text-2xl shadow-xl transition-all active:scale-95 btn-shimmer flex items-center justify-center gap-2">
                        <span>অর্ডার নিশ্চিত করুন</span>
                        <svg id="btn-spinner" class="hidden animate-spin h-6 w-6 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </button>

                    <p class="text-center text-xs text-gray-500 flex items-center justify-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                        আপনার তথ্য আমাদের কাছে ১০০% নিরাপদ
                    </p>
                </form>
            </div>

            <!-- Reviews / Social Proof -->
            <div class="mt-16 text-center">
                <h3 class="text-xl font-bold mb-8">আমাদের কাস্টমাররা কী বলছেন?</h3>
                <div class="space-y-6">
                    <div class="bg-white p-4 rounded-2xl shadow-md border border-gray-100 text-left">
                        <div class="flex items-center gap-2 mb-2 text-yellow-400">
                            ★★★★★
                        </div>
                        <p class="italic text-gray-600">"খুবই ভালো প্রোডাক্ট, ঠিক যেমনটা ভিডিওতে দেখেছি ডেলিভারি ও বেশ ফাস্ট ছিল"</p>
                        <p class="mt-2 font-bold text-sm text-gray-500">— সুমাইয়া আক্তার</p>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t py-10 px-4 text-center">
        <p class="text-gray-500 text-sm">© {{ date('Y') }} {{ $store->name }}. All Rights Reserved.</p>
        <div class="mt-4 flex justify-center gap-4 text-xs text-indigo-500 font-bold">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms & Conditions</a>
            <a href="#">Refund Policy</a>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function updateTotal(fee) {
            const basePrice = {{ $funnel['sale_price'] }};
            $('#shipping-fee').text('৳' + fee);
            $('#grand-total').text('৳' + (basePrice + fee));
            
            if(fee === 70) {
                $('#label-inside').addClass('border-indigo-500 bg-indigo-50').removeClass('border-gray-200');
                $('#label-outside').addClass('border-gray-200').removeClass('border-indigo-500 bg-indigo-50');
            } else {
                $('#label-outside').addClass('border-indigo-500 bg-indigo-50').removeClass('border-gray-200');
                $('#label-inside').addClass('border-gray-200').removeClass('border-indigo-500 bg-indigo-50');
            }
        }

        // Countdown Timer
        function startTimer(duration) {
            var timer = duration, hours, minutes, seconds;
            setInterval(function () {
                hours = parseInt(timer / 3600, 10);
                minutes = parseInt((timer % 3600) / 60, 10);
                seconds = parseInt(timer % 60, 10);

                hours = hours < 10 ? "0" + hours : hours;
                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                $('#timer').text(hours + ":" + minutes + ":" + seconds);

                if (--timer < 0) { timer = duration; }
            }, 1000);
        }
        startTimer(3600 * 12); // 12 hours

        // --- Fraud Shield Integration ---
        let isBlockedByFraud = false;
        let fraudCheckTimeout = null;

        $('#checkout-phone').on('input', function() {
            const phone = $(this).val();
            clearTimeout(fraudCheckTimeout);
            
            if (phone.length >= 11) {
                fraudCheckTimeout = setTimeout(() => {
                    checkFraud(phone);
                }, 800);
            }
        });

        function checkFraud(phone) {
            $('#phone-checking-loader').removeClass('hidden');
            $.ajax({
                url: '{{ route("fraud.live.check", $store->slug) }}',
                method: 'POST',
                data: { phone: phone, _token: '{{ csrf_token() }}' },
                success: function(res) {
                    $('#phone-checking-loader').addClass('hidden');
                    if (res.blocked) {
                        isBlockedByFraud = true;
                        showSoftLockPopup(res.whatsapp_url);
                    } else {
                        isBlockedByFraud = false;
                    }
                },
                error: function() { $('#phone-checking-loader').addClass('hidden'); }
            });
        }

        function showSoftLockPopup(waUrl) {
            const popupHtml = `
                <div id="fraud-modal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <div class="bg-white rounded-3xl p-8 max-w-sm w-full shadow-2xl text-center transform transition-all scale-100">
                        <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="text-4xl">🛒</span>
                        </div>
                        <h3 class="text-2xl font-bold mb-4">অর্ডারটি প্রসেস করতে পারছি না</h3>
                        <p class="text-gray-600 mb-8 leading-relaxed">
                            কারিগরি বা অপ্রত্যাশিত সমস্যার কারণে ওয়েবসাইট থেকে সরাসরি অর্ডারটি সম্পন্ন করা সম্ভব হচ্ছে না আপনার পছন্দের প্রোডাক্টটি দ্রুত নিশ্চিত করতে অনুগ্রহ করে আমাদের WhatsApp সাপোর্টে একটি মেসেজ দিন
                        </p>
                        <a href="${waUrl}" target="_blank" class="block w-full bg-green-500 hover:bg-green-600 text-white font-bold py-4 rounded-2xl shadow-lg transition-all active:scale-95 mb-4">
                            WhatsApp-এ অর্ডার দিন
                        </a>
                        <button onclick="$('#fraud-modal').remove()" class="text-gray-400 text-sm font-medium hover:text-gray-600">
                            পরে চেষ্টা করবো
                        </button>
                    </div>
                </div>
            `;
            $('body').append(popupHtml);
        }

        // --- Order Submission ---
        $('#funnel-checkout-form').on('submit', function(e) {
            e.preventDefault();
            
            if (isBlockedByFraud) {
                // Trigger modal again if they try to submit
                checkFraud($('#checkout-phone').val());
                return;
            }

            const $btn = $('#submit-btn');
            const $spinner = $('#btn-spinner');
            
            $btn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            $spinner.removeClass('hidden');

            $.ajax({
                url: '{{ route("funnel.order.submit", $store->slug) }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.status === 'success') {
                        // Meta Pixel Track Purchase (Conditional Isolation)
                        if (typeof fbq === 'function') {
                            if (res.pixel_isolation) {
                                fbq('trackCustom', 'OrderReceivedHold', { 
                                    content_ids: ['{{ $product->id }}'], 
                                    value: $('#grand-total').text().replace('৳', ''), 
                                    currency: 'BDT', 
                                    status: 'SuppressedForConfirmation' 
                                });
                            } else {
                                fbq('track', 'Purchase', { 
                                    content_ids: ['{{ $product->id }}'], 
                                    value: $('#grand-total').text().replace('৳', ''), 
                                    currency: 'BDT' 
                                });
                            }
                        }

                        // Redirect or Show Success
                        showOrderSuccess(res.order_id);
                    } else {
                        alert(res.message);
                        $btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                        $spinner.addClass('hidden');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'অর্ডারটি ব্যর্থ হয়েছে';
                    alert(msg);
                    $btn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    $spinner.addClass('hidden');
                }
            });
        });

        function showOrderSuccess(orderId) {
            const successHtml = `
                <div class="fixed inset-0 z-[100] bg-white flex flex-col items-center justify-center p-6 text-center">
                    <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h2 class="text-3xl font-bold text-green-600 mb-2">অর্ডার সফল হয়েছে!</h2>
                    <p class="text-xl font-medium mb-6">আপনার অর্ডার আইডি: <span class="text-indigo-600">#${orderId}</span></p>
                    <p class="text-gray-500 mb-10 leading-relaxed max-w-sm">
                        আমাদের প্রতিনিধি শীঘ্রই ফোন করে আপনার অর্ডারটি কনফার্ম করবেন আমাদের কল রিসিভ করতে ফোনটি সচল রাখুন ধন্যবাদ!
                    </p>
                    <a href="/" class="bg-gray-800 text-white px-8 py-4 rounded-2xl font-bold shadow-lg">আরও প্রোডাক্ট দেখুন</a>
                </div>
            `;
            $('body').html(successHtml);
            window.scrollTo(0, 0);
        }
    </script>
</body>
</html>
