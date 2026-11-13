@extends('front_end.layouts.app')
@section('page-title')
    {{ __('Order Success') }}
@endsection
@section('content')
<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card border-0 shadow-sm p-5">
                <div class="mb-4">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h2 class="fw-bold mb-3">{{ __('ধন্যবাদ! আপনার অর্ডারটি সফলভাবে সম্পন্ন হয়েছে') }}</h2>
                <p class="text-muted mb-4">
                    {{ __('আমাদের প্রতিনিধি শীঘ্রই আপনার সাথে ফোনে যোগাযোগ করে অর্ডারটি কনফার্ম করবেন') }}
                </p>
                
                <div class="order-details bg-light p-4 rounded-3 text-start mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('অর্ডার আইডি:') }}</span>
                        <span class="fw-bold">#{{ $order->product_order_id }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('প্রোডাক্ট:') }}</span>
                        <span class="fw-bold">{{ $product->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('মোট মূল্য:') }}</span>
                        <span class="fw-bold">{{ \App\Models\Utility::priceFormat($order->final_price) }}</span>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('store.slug', $store->slug) }}" class="btn btn-primary btn-lg rounded-pill">
                        {{ __('শপিং চালিয়ে যান') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
