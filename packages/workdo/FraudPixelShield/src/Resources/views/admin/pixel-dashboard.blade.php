@extends('layouts.app')

@section('page-title', __('Meta Pixel Isolation & Delayed CAPI'))

@section('action-button')
    <a href="{{ route('fraud.pixel.capi.logs') }}" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
        <i class="ti ti-history"></i> {{ __('CAPI Dispatch Logs') }}
    </a>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Smart Features') }}</li>
    <li class="breadcrumb-item active">{{ __('Pixel Isolation & CAPI') }}</li>
@endsection

@section('content')
<div class="row g-4">
    {{-- Top Hero Banner with Live On/Off Toggle --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #312e81 100%); color: #fff;">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <span class="badge bg-primary-subtle text-primary px-3 py-1 mb-2 rounded-pill font-weight-bold" style="background: rgba(99, 102, 241, 0.2) !important; color: #a5b4fc !important;">
                        <i class="ti ti-brand-facebook"></i> Meta Pixel Shield & Delayed CAPI
                    </span>
                    <h2 class="h3 font-weight-bold m-0 text-white">{{ __('Meta Pixel Isolation & Conversion Guard') }}</h2>
                    <p class="text-white-50 m-0 mt-1">
                        {{ __('অর্ডার সম্পন্ন হওয়ার সাথে সাথে ব্রাউজার পিক্সেলে Purchase পাঠানো বন্ধ রাখুন। অর্ডার কনফার্ম বা ডেলিভারি নিশ্চিত হলে তবেই ব্যাকএন্ড থেকে সার্ভার-সাইড CAPI-তে পাঠান।') }}
                    </p>
                </div>
                <div class="d-flex align-items-center gap-3 bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10">
                    <div>
                        <span class="d-block text-xs text-white-50 text-uppercase font-weight-bold">{{ __('Isolation Mode') }}</span>
                        <span class="h6 font-weight-bold m-0 text-white" id="isolation-status-text">
                            {{ $isIsolationActive ? __('সক্রিয় (Purchase হোল্ড)') : __('নিষ্ক্রিয় (সরাসরি পাঠানো)') }}
                        </span>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="pixel-isolation-toggle" 
                               style="width: 3rem; height: 1.5rem; cursor: pointer;"
                               {{ $isIsolationActive ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4 Calculation Metric Cards --}}
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 text-white" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <i class="ti ti-lock" style="font-size: 24px;"></i>
                </div>
                <div>
                    <span class="text-xs text-uppercase font-weight-bold text-muted d-block">{{ __('Purchases Held') }}</span>
                    <h3 class="h3 font-weight-bold m-0 text-dark">{{ $totalHeld }}</h3>
                    <small class="text-muted">{{ __('ব্রাউজারে সাপ্রেস করা অর্ডার') }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 text-white" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <i class="ti ti-circle-check" style="font-size: 24px;"></i>
                </div>
                <div>
                    <span class="text-xs text-uppercase font-weight-bold text-muted d-block">{{ __('Dispatched to CAPI') }}</span>
                    <h3 class="h3 font-weight-bold m-0 text-success">{{ $totalDispatched }}</h3>
                    <small class="text-muted">{{ __('মেটা সার্ভারে সফলভাবে পাঠানো') }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 text-white" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
                    <i class="ti ti-clock-pause" style="font-size: 24px;"></i>
                </div>
                <div>
                    <span class="text-xs text-uppercase font-weight-bold text-muted d-block">{{ __('Pending Confirmation') }}</span>
                    <h3 class="h3 font-weight-bold m-0 text-primary">{{ $pendingConfirm }}</h3>
                    <small class="text-muted">{{ __('কনফার্মেশনের অপেক্ষায়') }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 text-white" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                    <i class="ti ti-currency-taka" style="font-size: 24px;"></i>
                </div>
                <div>
                    <span class="text-xs text-uppercase font-weight-bold text-muted d-block">{{ __('Est. Ad Spend Saved') }}</span>
                    <h3 class="h3 font-weight-bold m-0 text-dark">৳{{ number_format($estimatedSaved) }}</h3>
                    <small class="text-muted">{{ __('ভুয়া অর্ডারে বাজেট অপচয় রোধ') }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Settings Card --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-title m-0 font-weight-bold text-dark d-flex align-items-center gap-2">
                    <i class="ti ti-settings text-primary"></i> {{ __('Meta Pixel & Conversions API (CAPI) Configuration') }}
                </h4>
            </div>

            <form action="{{ route('fraud.pixel.settings.update') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6 col-12">
                        <label class="form-label font-weight-bold">{{ __('Meta Pixel ID') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="ti ti-hash"></i></span>
                            <input type="text" name="meta_pixel_id" class="form-control bg-light border-start-0" 
                                   value="{{ $pixelId }}" placeholder="e.g. 123456789012345" />
                        </div>
                        <small class="text-muted">{{ __('আপনার ফেসবুক ইভেন্টস ম্যানেজার থেকে মেটা পিক্সেল আইডি দিন') }}</small>
                    </div>

                    <div class="col-md-6 col-12">
                        <label class="form-label font-weight-bold">{{ __('Meta CAPI System User Access Token') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="ti ti-key"></i></span>
                            <input type="password" name="meta_capi_access_token" class="form-control bg-light border-start-0" 
                                   value="{{ $capiToken }}" placeholder="EAAG..." />
                        </div>
                        <small class="text-muted">{{ __('সার্ভার-সাইড ট্র্যাকিংয়ের জন্য ফেসবুক সিস্টেম ইউজার এক্সেস টোকেন') }}</small>
                    </div>

                    <div class="col-md-6 col-12">
                        <label class="form-label font-weight-bold">{{ __('Dispatch CAPI Purchase When') }}</label>
                        <select name="delay_capi_until" class="form-select bg-light">
                            <option value="order_confirm" {{ $delayUntil === 'order_confirm' ? 'selected' : '' }}>
                                {{ __('Order Confirmed by Admin (অর্ডার কনফার্ম করার পর)') }}
                            </option>
                            <option value="delivery_confirm" {{ $delayUntil === 'delivery_confirm' ? 'selected' : '' }}>
                                {{ __('Order Delivered / Completed (কুরিয়ারে ডেলিভারি হওয়ার পর)') }}
                            </option>
                        </select>
                        <small class="text-muted">{{ __('কোন ধাপে পৌঁছালে মেটা সার্ভারে Purchase ইভেন্ট পাঠানো হবে') }}</small>
                    </div>

                    <div class="col-md-6 col-12 d-flex align-items-end justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2">
                            <i class="ti ti-device-floppy"></i> {{ __('Save Settings') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Orders Queue Table with Confirm & Send to Meta Button --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h4 class="card-title m-0 font-weight-bold text-dark d-flex align-items-center gap-2">
                    <i class="ti ti-list-check text-primary"></i> {{ __('Recent Orders & Pixel Dispatch Ledger') }}
                </h4>
                <span class="badge bg-light text-muted border px-3 py-1">{{ __('Total Orders: ') }} {{ $orders->total() }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Order ID') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('Total Amount') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Pixel Status') }}</th>
                            <th>{{ __('CAPI Status') }}</th>
                            <th class="text-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="font-weight-bold">
                                    <a href="{{ route('order.view', \Crypt::encrypt($order->id)) }}" class="text-primary text-decoration-none">
                                        #{{ $order->product_order_id ?? $order->id }}
                                    </a>
                                </td>
                                <td>{{ $order->billingData->first_name ?? ($order->CustomerData->first_name ?? ($order->user_name ?? 'Guest')) }}</td>
                                <td class="font-weight-bold">{{ $order->billingData->telephone ?? ($order->CustomerData->mobile ?? '-') }}</td>
                                <td class="font-weight-bold">৳{{ number_format($order->final_price ?? ($order->grand_total ?? 0)) }}</td>
                                <td>{{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : '-' }}</td>
                                <td>
                                    @if(($order->pixel_status ?? 'hold') === 'dispatched')
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                            <i class="ti ti-circle-check"></i> DISPATCHED
                                        </span>
                                    @elseif(($order->pixel_status ?? 'hold') === 'active')
                                        <span class="badge bg-info-subtle text-info border border-info px-2 py-1">
                                            <i class="ti ti-bolt"></i> DIRECT
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1" style="background: #fffbeb; color: #b45309;">
                                            <i class="ti ti-lock"></i> HOLD (Suppressed)
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if(($order->capi_status ?? 'pending') === 'dispatched')
                                        <span class="badge bg-success">{{ __('Dispatched') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Pending') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(($order->pixel_status ?? 'hold') !== 'dispatched')
                                        <form action="{{ route('fraud.pixel.capi.trigger', $order->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1"
                                                    title="{{ __('Confirm order and immediately dispatch real Purchase to Meta CAPI') }}">
                                                <i class="ti ti-send"></i> {{ __('Confirm & Send to Meta') }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-success small font-weight-bold d-inline-flex align-items-center gap-1">
                                            <i class="ti ti-circle-check"></i> {{ __('Sent to Meta') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">{{ __('No orders found yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>

@push('page-style')
<script>
window.onTurboLoad(function () {
    const toggle = document.getElementById('pixel-isolation-toggle');
    const statusText = document.getElementById('isolation-status-text');

    if (!toggle) return;

    toggle.addEventListener('change', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        
        fetch("{{ route('fraud.pixel.toggle') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                toggle.checked = data.enabled;
                if (statusText) {
                    statusText.innerText = data.enabled 
                        ? 'সক্রিয় (Purchase হোল্ড)' 
                        : 'নিষ্ক্রিয় (সরাসরি পাঠানো)';
                }
                show_toastr('Success', data.message, 'success');
            } else {
                show_toastr('Error', data.message || 'Error occurred', 'error');
            }
        })
        .catch(err => {
            show_toastr('Error', err.message, 'error');
        });
    });
});
</script>
@endpush
@endsection
