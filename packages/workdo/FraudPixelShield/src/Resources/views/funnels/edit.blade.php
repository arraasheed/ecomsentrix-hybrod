@extends('layouts.app')

@section('page-title', 'ফানেল অফার সম্পাদনা: ' . $funnel->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">ফানেল অফার সম্পাদনা</h1>
            <p class="text-gray-500 mt-1">"{{ $funnel->title }}" - Slug: {{ $funnel->slug }}</p>
        </div>
        <a href="{{ route('funnel.landing', ['storeSlug' => $store->slug ?? 'demo', 'slug' => $funnel->slug]) }}" target="_blank" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 text-sm font-medium transition-colors inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            লাইভ প্রিভিউ
        </a>
    </div>

    <form action="{{ route('fraud.funnel.update', $funnel->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" id="funnelForm">
        @csrf
        @method('PUT')

        {{-- Basic Info --}}
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" style="color: {{ $funnel->theme_color ?? '#4f46e5' }};" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                মুল তথ্য
            </h2>
            
            <div class="space-y-5">
                <div>
                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">প্রোডাক্ট <span class="text-red-500">*</span></label>
                    <select name="product_id" id="product_id" class="input-field w-full px-4 py-3 rounded-xl border border-gray-300 bg-white text-lg transition-all" required>
                        <option value="">প্রোডাক্ট নির্বাচন করুন</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ $funnel->product_id == $product->id ? 'selected' : '' }}>{{ $product->name }} - {{ \Modules\Ecommerce\Entities\Currency::formatPrice($product->price) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">অফার টাইটেল <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" value="{{ $funnel->title }}" class="input-field w-full px-4 py-3 rounded-xl border border-gray-300 bg-white text-lg placeholder-gray-400 transition-all" placeholder="যেমন: লিমিটেড অফার - ৫০% ছাড় সহ ফ্রি ডেলিভারি" required maxlength="255">
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug (URL) <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <span class="px-4 py-3 bg-gray-100 rounded-xl text-gray-500 flex items-center">{{ url('/') }}/offer/</span>
                        <input type="text" name="slug" id="slug" value="{{ $funnel->slug }}" class="input-field flex-1 px-4 py-3 rounded-xl border border-gray-300 bg-white text-lg placeholder-gray-400 transition-all" placeholder="limited-offer-50-off" required maxlength="255">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">এটি পরিবর্তন করলে পুরনো লিংক কাজ করবে না</p>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">বিবরণ</label>
                    <textarea name="description" id="description" rows="3" class="input-field w-full px-4 py-3 rounded-xl border border-gray-300 bg-white text-lg placeholder-gray-400 transition-all resize-none" placeholder="অফারের সংক্ষিপ্ত বিবরণ...">{{ $funnel->description }}</textarea>
                </div>
            </div>
        </div>

        {{-- Media & Countdown --}}
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" style="color: {{ $funnel->theme_color ?? '#4f46e5' }};" fill="currentColor" viewBox="0 0 24 24"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                মিডিয়া ও কাউন্টডাউন
            </h2>
            
            <div class="space-y-5">
                <div>
                    <label for="video_url" class="block text-sm font-medium text-gray-700 mb-2">ভিডিও URL (YouTube, Vimeo, Facebook)</label>
                    <input type="url" name="video_url" id="video_url" value="{{ $funnel->video_url }}" class="input-field w-full px-4 py-3 rounded-xl border border-gray-300 bg-white text-lg placeholder-gray-400 transition-all" placeholder="https://youtube.com/watch?v=... বা https://vimeo.com/...">
                    <p class="text-xs text-gray-500 mt-1">হিরো সেকশনে ভিডিও দেখাবে (এম্বেড অটো-হ্যান্ডেল হবে)</p>
                </div>

                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label for="countdown_end" class="block text-sm font-medium text-gray-700 mb-2">কাউন্টডাউন শেষ হওয়ার সময়</label>
                        <input type="datetime-local" name="countdown_end" id="countdown_end" value="{{ $funnel->countdown_end ? \Carbon\Carbon::parse($funnel->countdown_end)->format('Y-m-d\TH:i') : '' }}" class="input-field w-full px-4 py-3 rounded-xl border border-gray-300 bg-white text-lg transition-all">
                    </div>
                    <div>
                        <label for="theme_color" class="block text-sm font-medium text-gray-700 mb-2">থিম রঙ (Hex Code)</label>
                        <div class="flex gap-3">
                            <input type="color" name="theme_color" id="theme_color" value="{{ $funnel->theme_color ?? '#4f46e5' }}" class="w-12 h-12 rounded-xl border border-gray-300 cursor-pointer" title="থিম রঙ পিক করুন">
                            <input type="text" name="theme_color_text" id="theme_color_text" value="{{ $funnel->theme_color ?? '#4f46e5' }}" class="input-field flex-1 px-4 py-3 rounded-xl border border-gray-300 bg-white text-lg font-mono transition-all" placeholder="#4f46e5">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Review Gallery --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5" style="color: {{ $funnel->theme_color ?? '#4f46e5' }};" fill="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    রিভিউ গ্যালারি (ক্যরোসেলে দেখাবে)
                </h2>
                <button type="button" id="addReviewBtn" class="px-4 py-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-sm font-medium transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    রিভিউ ছবি যোগ করুন
                </button>
            </div>
            
            <div id="reviewImagesContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 mb-4">
                {{-- Existing review images --}}
                @php
                    $reviewGallery = $funnel->review_gallery ?? [];
                    $existingImages = is_string($reviewGallery) ? json_decode($reviewGallery, true) : $reviewGallery;
                    $legacyFormat = false;
                    if (is_array($existingImages)) {
                        foreach ($existingImages as $item) {
                            if (is_array($item) && (isset($item['name']) || isset($item['rating']) || isset($item['comment']))) {
                                $legacyFormat = true;
                                break;
                            }
                        }
                    }
                @endphp
                
                @if(!empty($existingImages) && !$legacyFormat)
                    @foreach($existingImages as $index => $imagePath)
                        @php $imgUrl = asset('storage/' . $imagePath); @endphp
                        <div class="relative group" data-index="{{ $index }}" data-existing="true">
                            <input type="hidden" name="existing_reviews[{{ $index }}][image]" value="{{ $imagePath }}">
                            <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 relative">
                                <img src="{{ $imgUrl }}" alt="Review {{ $index + 1 }}" class="w-full h-full object-cover">
                                <button type="button" class="remove-review absolute top-1 right-1 w-7 h-7 rounded-full bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center" title="মুছুন">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            
            {{-- Legacy review format display --}}
            @if(!empty($existingImages) && $legacyFormat)
                <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                    <p class="text-sm text-yellow-800 mb-2">পুরানো ফরম্যাটের রিভিউ ডেটা পাওয়া গেছে (নাম, রেটিং, মন্তব্য সহ) নতুন ছবি আপলোড করলে এগুলো রিপ্লেস হবে</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        @foreach($existingImages as $index => $review)
                            @if(is_array($review) && isset($review['name']))
                                <div class="p-3 bg-white rounded-lg border text-center text-sm">
                                    <p class="font-medium text-gray-900">{{ $review['name'] }}</p>
                                    <p class="text-yellow-500">{{ str_repeat('★', $review['rating'] ?? 5) }}</p>
                                    @if(!empty($review['comment']))
                                    <p class="text-gray-600 line-clamp-2 mt-1">{{ $review['comment'] }}</p>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
            
            <div class="p-4 bg-gray-50 rounded-xl text-sm text-gray-600">
                <p><strong>টিপস:</strong> মিনিমাম ৩-৫টি রিভিউ ছবি আপলোড করুন কনভার্সন বাড়াতে প্রতিটি ছবি সর্বোচ্চ ২MB পুরনো ছবি মুছে নতুন দিতে চাইলে "মুছুন" বাটন চাপুন</p>
            </div>
        </div>

        {{-- Status --}}
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" style="color: {{ $funnel->theme_color ?? '#4f46e5' }};" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                স্ট্যাটাস
            </h2>
            
            <div class="flex gap-6">
                <label class="relative flex items-center p-4 rounded-xl border-2 {{ $funnel->status === 'active' ? 'border-primary-theme bg-primary-50' : 'border-gray-200' }} cursor-pointer hover:border-gray-300 transition-all">
                    <input type="radio" name="status" value="active" class="sr-only" {{ $funnel->status === 'active' ? 'checked' : '' }}>
                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full relative mr-3 flex-shrink-0 {{ $funnel->status === 'active' ? 'border-primary-theme' : '' }} {{ $funnel->status === 'active' ? 'bg-primary-theme' : '' }}"></div>
                    <div>
                        <span class="font-medium text-gray-900">সক্রিয় (Published)</span>
                        <p class="text-xs text-gray-500 mt-0.5">পাবলিকলি এক্সেসযোগ্য থাকবে</p>
                    </div>
                </label>
                
                <label class="relative flex items-center p-4 rounded-xl border-2 {{ $funnel->status === 'inactive' ? 'border-primary-theme bg-primary-50' : 'border-gray-200' }} cursor-pointer hover:border-gray-300 transition-all">
                    <input type="radio" name="status" value="inactive" class="sr-only" {{ $funnel->status === 'inactive' ? 'checked' : '' }}>
                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full relative mr-3 flex-shrink-0 {{ $funnel->status === 'inactive' ? 'border-primary-theme' : '' }} {{ $funnel->status === 'inactive' ? 'bg-primary-theme' : '' }}"></div>
                    <div>
                        <span class="font-medium text-gray-900">নিষ্ক্রিয় (Draft)</span>
                        <p class="text-xs text-gray-500 mt-0.5">শুধু অ্যাডমিনে দেখা যাবে</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-100">
            <a href="{{ route('fraud.funnel.index') }}" class="px-6 py-3 rounded-xl font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors text-center">বাতিল</a>
            <button type="submit" class="btn-primary-theme px-8 py-3 rounded-xl font-semibold text-white flex items-center justify-center gap-2" style="background: linear-gradient(135deg, {{ $funnel->theme_color ?? '#4f46e5' }}, color-mix(in srgb, {{ $funnel->theme_color ?? '#4f46e5' }} 70%, black));">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                আপডেট করুন
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Theme color sync
    const colorPicker = document.getElementById('theme_color');
    const colorText = document.getElementById('theme_color_text');

    colorPicker.addEventListener('input', function() {
        colorText.value = this.value;
    });

    colorText.addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            colorPicker.value = this.value;
        }
    });

    // Review image upload
    const reviewContainer = document.getElementById('reviewImagesContainer');
    const addReviewBtn = document.getElementById('addReviewBtn');
    let reviewCount = document.querySelectorAll('#reviewImagesContainer > [data-index]').length;

    function createReviewItem(file = null, previewUrl = null) {
        const index = reviewCount++;
        const div = document.createElement('div');
        div.className = 'relative group';
        div.dataset.index = index;
        
        div.innerHTML = `
            <input type="hidden" name="review_images[${index}]" class="review-file-input">
            <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 relative">
                ${previewUrl ? `
                    <img src="${previewUrl}" alt="Review ${index + 1}" class="w-full h-full object-cover">
                ` : `
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                `}
                <button type="button" class="remove-review absolute top-1 right-1 w-7 h-7 rounded-full bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center" title="মুছুন">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <input type="file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="handleReviewFile(this, ${index})">
        `;
        
        reviewContainer.appendChild(div);
        
        if (file) {
            const fileInput = div.querySelector('input[type="file"]');
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
            handleReviewFile(fileInput, index);
        }
        
        return div;
    }

    function handleReviewFile(input, index) {
        const file = input.files[0];
        if (!file) return;
        
        if (file.size > 2 * 1024 * 1024) {
            alert('ছবি ২MB এর বেশি হতে পারে না');
            input.value = '';
            return;
        }
        
        const div = input.closest('[data-index]');
        const img = div.querySelector('img') || document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.className = 'w-full h-full object-cover';
        
        const previewDiv = div.querySelector('.aspect-square');
        previewDiv.innerHTML = '';
        previewDiv.appendChild(img);
        
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'remove-review absolute top-1 right-1 w-7 h-7 rounded-full bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center';
        removeBtn.title = 'মুছুন';
        removeBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
        previewDiv.appendChild(removeBtn);
        
        // Update hidden input
        const hiddenInput = div.querySelector('.review-file-input');
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        hiddenInput.files = dataTransfer.files;
        
        // Remove existing flag if it was an existing image
        div.removeAttribute('data-existing');
    }

    // Event delegation for remove buttons
    reviewContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-review')) {
            const item = e.target.closest('[data-index]');
            const isExisting = item.dataset.existing === 'true';
            
            if (isExisting) {
                // For existing images, hide and mark for deletion
                item.style.display = 'none';
                const hiddenInput = item.querySelector('input[name^="existing_reviews"]');
                if (hiddenInput) {
                    hiddenInput.name = hiddenInput.name.replace('existing_reviews', 'delete_reviews');
                }
            } else {
                item.remove();
            }
        }
    });

    addReviewBtn.addEventListener('click', function() {
        if (reviewCount >= 10) {
            alert('সর্বোচ্চ ১০টি রিভিউ ছবি যোগ করা যাবে');
            return;
        }
        createReviewItem();
    });

    // Form validation
    document.getElementById('funnelForm').addEventListener('submit', function(e) {
        const productId = document.getElementById('product_id').value;
        const title = document.getElementById('title').value.trim();
        const slug = document.getElementById('slug').value.trim();
        
        if (!productId || !title || !slug) {
            e.preventDefault();
            alert('অনুগ্রহ করে প্রোডাক্ট, টাইটেল এবং Slug পূরণ করুন');
            return false;
        }
        
        if (!/^[a-z0-9-]+$/.test(slug)) {
            e.preventDefault();
            alert('Slug শুধু ইংরেজি ছোট হাতের অক্ষর, সংখ্যা এবং হাইফন (-) থাকতে পারে');
            return false;
        }
    });
</script>
@endpush