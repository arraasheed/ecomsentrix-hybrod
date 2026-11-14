@extends('layouts.app')

@section('page-title', 'ফানেল অফার ম্যানেজমেন্ট')

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">ফানেল অফার ম্যানেজমেন্ট</h1>
            <p class="text-gray-500 mt-1">ল্যান্ডিং পেজ ফানেল তৈরি ও পরিচালনা করুন ১-ক্লিক চেকআউট সহ</p>
        </div>
        <a href="{{ route('fraud.funnel.create') }}" class="btn-primary-theme px-6 py-3 rounded-xl font-semibold text-white inline-flex items-center gap-2 w-full sm:w-auto justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            নতুন ফানেল তৈরি
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500 font-medium">মোট ফানেল</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $funnels->total() }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500 font-medium">সক্রিয়</p>
            <p class="text-3xl font-bold text-green-600 mt-1">{{ $funnels->where('status', 'active')->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500 font-medium">মোট ভিউ</p>
            <p class="text-3xl font-bold text-blue-600 mt-1">{{ $funnels->sum('views_count') }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500 font-medium">মোট অর্ডার</p>
            <p class="text-3xl font-bold text-purple-600 mt-1">{{ $funnels->sum('orders_count') }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($funnels->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">ফানেল</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">প্রোডাক্ট</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">স্ট্যাটাস</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">ভিউ</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">অর্ডার</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">কনভার্সন</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($funnels as $funnel)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, {{ $funnel->theme_color ?? '#4f46e5' }}, color-mix(in srgb, {{ $funnel->theme_color ?? '#4f46e5' }} 70%, black));">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $funnel->title }}</p>
                                    <p class="text-xs text-gray-500">Slug: {{ $funnel->slug }}</p>
                                    <p class="text-xs text-gray-400">ID: {{ $funnel->id }} | {{ $funnel->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($funnel->product)
                            <div class="flex items-center gap-3">
                                @if($funnel->product->image)
                                <img src="{{ asset('storage/' . $funnel->product->image) }}" alt="{{ $funnel->product->name }}" class="w-12 h-12 rounded-lg object-cover">
                                @else
                                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xs">No Image</div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900">{{ $funnel->product->name }}</p>
                                    <p class="text-sm" style="color: {{ $funnel->theme_color ?? '#4f46e5' }};">{{ \Modules\Ecommerce\Entities\Currency::formatPrice($funnel->product->price) }}</p>
                                </div>
                            </div>
                            @else
                            <span class="text-gray-400">প্রোডাক্ট মুছে ফেলা হয়েছে</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                @if($funnel->status === 'active')
                                    bg-green-100 text-green-800
                                @else
                                    bg-gray-100 text-gray-800
                                @endif">
                                @if($funnel->status === 'active')
                                    <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    সক্রিয়
                                @else
                                    <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    নিষ্ক্রিয়
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center font-mono font-medium text-gray-900">{{ $funnel->views_count }}</td>
                        <td class="px-6 py-4 text-center font-mono font-medium text-gray-900">{{ $funnel->orders_count }}</td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $conversion = $funnel->views_count > 0 ? round(($funnel->orders_count / $funnel->views_count) * 100, 2) : 0;
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                @if($conversion >= 5)
                                    bg-green-100 text-green-800
                                @elseif($conversion >= 2)
                                    bg-yellow-100 text-yellow-800
                                @else
                                    bg-gray-100 text-gray-800
                                @endif">
                                {{ $conversion }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('funnel.landing', ['storeSlug' => $store->slug ?? 'demo', 'slug' => $funnel->slug]) }}" target="_blank" class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="প্রিভিউ">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="{{ route('fraud.funnel.edit', $funnel->id) }}" class="p-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors" title="সম্পাদনা">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form action="{{ route('fraud.funnel.destroy', $funnel->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই ফানেলটি মুছতে চান?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="মুছুন">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $funnels->links() }}
        </div>
        @else
        <div class="text-center py-16">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            <h3 class="text-lg font-medium text-gray-900 mb-1">কোনো ফানেল অফার নেই</h3>
            <p class="text-gray-500 mb-6">আপনার প্রথম ১-ক্লিক চেকআউট ল্যান্ডিং পেজ তৈরি করুন</p>
            <a href="{{ route('fraud.funnel.create') }}" class="btn-primary-theme px-6 py-3 rounded-xl font-semibold text-white inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                ফানেল তৈরি করুন
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-hide flash messages
    setTimeout(() => {
        const alerts = document.querySelectorAll('[role="alert"]');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
</script>
@endpush