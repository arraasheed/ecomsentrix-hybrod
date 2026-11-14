@extends('layouts.app')

@section('page-title', 'নতুন ফানেল অফার তৈরি')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">নতুন ফানেল অফার তৈরি</h1>
        <p class="text-gray-500 mt-1">১-ক্লিক চেকআউট সহ হাই-কনভার্টিং ল্যান্ডিং পেজ বানান</p>
    </div>

    <form action="{{ route('fraud.funnel.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" id="funnelForm">
        @csrf

        {{-- Basic Info --}}
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" style="color: var(--theme-primary);" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                মুল তথ্য
            </h2>
            
            <div class="space-y-5">
                <div>
                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">প্রোডাক্ট <span class="text-red-500">*</span></label>
                    <select name="product_id" id="product_id" class="input-field w-full px-4 py-3 rounded-xl border border-gray-300 bg-white text-lg transition-all" required>
                        <option value="">প্রোডাক্ট নির্বাচন করুন</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} - {{ \Modules\Ecommerce\Entities\Currency::formatPrice($product->price) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">অফার টাইটেল <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" class="input-field w-full px-4 py-3 rounded-xl border border-gray-300 bg-white text-lg placeholder-gray-400 transition-all" placeholder="যেমন: লিমিটেড অফার - ৫০% ছাড় সহ ফ্রি ডেলিভারি" required maxlength="255">
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug (URL) <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <span class="px-4 py-3 bg-gray-100 rounded-xl text-gray-500 flex items-center">{{ url('/') }}/offer/</span>
                        <input type="text" name="slug" id="slug" class="input-field flex-1 px-4 py-3 rounded-xl border border-gray-300 bg-white text-lg placeholder-gray-400 transition-all" placeholder="limited-offer-50-off" required maxlength="255">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">অটো জেনারেট হবে টাইটেল থেকে ছাড় দিয়ে ইংরেজি ছোট হাতের অক্ষরে</p>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">বিবরণ</label>
                    <textarea name="description" id="description" rows="3" class="input-field w-full px-4 py-3 rounded-xl border border-gray-300 bg-white text-lg placeholder-gray-400 transition-all resize-none" placeholder="অফারের সংক্ষিপ্ত বিবরণ..."></textarea>
                </div>
            </div>
        </div>

        {{-- Media & Countdown --}}
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" style="color: var(--theme-primary);" fill="currentColor" viewBox="0 0 24 24"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                মিডিয়া ও কাউন্টডাউন
            </h2>
            
            <div class="space-y-5">
                <div>
                    <label for="video_url" class="block text-sm font-medium text-gray-700 mb-2">ভিডিও URL (YouTube, Vimeo, Facebook)</label>
                    <input type="url" name="video_url" id="video_url" class="input-field w-full px-4 py-3 rounded-xl border border-gray-300 bg-white text-lg placeholder-gray-400 transition-all" placeholder="https://youtube.com/watch?v=... বা https://vimeo.com/...">
                    <p class="text-xs text-gray-500 mt-1">হিরো সেকশনে ভিডিও দেখাবে (এম্বেড অটো-হ্যান্ডেল হবে)</p>
                </div>

                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label for="countdown_end" class="block text-sm font-medium text-gray-700 mb-2">কাউন্টডাউন শেষ হওয়ার সময়</label>
                        <input type="datetime-local" name="countdown_end" id="countdown_end" class="input-field w-full px-4 py-3 rounded-xl border border-gray-300 bg-white text-lg transition-all">
                    </div>
                    <div>
                        <label for="theme_color" class="block text-sm font-medium text-gray-700 mb-2">থিম রঙ (Hex Code)</label>
                        <div class="flex gap-3">
                            <input type="color" name="theme_color" id="theme_color" value="#4f46e5" class="w-12 h-12 rounded-xl border border-gray-300 cursor-pointer" title="থিম রঙ পিক করুন">
                            <input type="text" name="theme_color_text" id="theme_color_text" value="#4f46e5" class="input-field flex-1 px-4 py-3 rounded-xl border border-gray-300 bg-white text-lg font-mono transition-all" placeholder="#4f46e5">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Review Gallery --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5" style="color: var(--theme-primary);" fill="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    রিভিউ গ্যালারি (ক্যরোসেলে দেখাবে)
                </h2>
                <button type="button" id="addReviewBtn" class="px-4 py-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-sm font-medium transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    রিভিউ ছবি যোগ করুন
                </button>
            </div>
            
            <div id="reviewImagesContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                {{-- Dynamic review images will be added here --}}
            </div>
            
            <div class="mt-4 p-4 bg-gray-50 rounded-xl text-sm text-gray-600">
                <p><strong>টিপস:</strong> মিনিমাম ৩-৫টি রিভিউ ছবি আপলোড করুন কনভার্সন বাড়াতে প্রতিটি ছবি সর্বোচ্চ ২MB</p>
            </div>
        </div>

        {{-- Status --}}
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" style="color: var(--theme-primary);" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                স্ট্যাটাস
            </h2>
            
            <div class="flex gap-6">
                <label class="relative flex items-center p-4 rounded-xl border-2 border-gray-200 cursor-pointer hover:border-gray-300 transition-all has-[:checked]:border-primary-theme has-[:checked]:bg-primary-50">
                    <input type="radio" name="status" value="active" class="sr-only" checked>
                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full relative mr-3 flex-shrink-0 has-[:checked]:border-primary-theme has-[:checked]:after:content-[''] has-[:checked]:after:absolute has-[:checked]:after:top-1/2 has-[:checked]:after:left-1/2 has-[:checked]:after:-translate-x-1/2 has-[:checked]:after:-translate-y-1/2 has-[:checked]:after:w-2 has-[:checked]:after:h-2 has-[:checked]:after:rounded-full" style="background-color: var(--theme-primary);"></div>
                    <div>
                        <span class="font-medium text-gray-900">সক্রিয় (Published)</span>
                        <p class="text-xs text-gray-500 mt-0.5">পাবলিকলি এক্সেসযোগ্য থাকবে</p>
                    </div>
                </label>
                
                <label class="relative flex items-center p-4 rounded-xl border-2 border-gray-200 cursor-pointer hover:border-gray-300 transition-all has-[:checked]:border-primary-theme has-[:checked]:bg-primary-50">
                    <input type="radio" name="status" value="inactive" class="sr-only">
                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full relative mr-3 flex-shrink-0 has-[:checked]:border-primary-theme has-[:checked]:after:content-[''] has-[:checked]:after:absolute has-[:checked]:after:top-1/2 has-[:checked]:after:left-1/2 has-[:checked]:after:-translate-x-1/2 has-[:checked]:after:-translate-y-1/2 has-[:checked]:after:w-2 has-[:checked]:after:h-2 has-[:checked]:after:rounded-full" style="background-color: var(--theme-primary);"></div>
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
            <button type="submit" class="btn-primary-theme px-8 py-3 rounded-xl font-semibold text-white flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                ফানেল তৈরি করুন
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Auto-generate slug from title
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    let slugManuallyEdited = false;

    titleInput.addEventListener('input', function() {
        if (!slugManuallyEdited) {
            const slug = this.value
                .toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug.substring(0, 255);
        }
    });

    slugInput.addEventListener('input', function() {
        slugManuallyEdited = true;
    });

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
    let reviewCount = 0;

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
        
        // If file provided, set it
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
    }

    // Event delegation for remove buttons
    reviewContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-review')) {
            e.target.closest('[data-index]').remove();
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