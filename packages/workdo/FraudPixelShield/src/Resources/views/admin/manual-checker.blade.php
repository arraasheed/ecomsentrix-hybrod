@extends('layouts.app')

@section('page-title', __('Live Courier Fraud Checker'))

@section('action-button')
    <a href="{{ route('fraud.shield.leads') }}" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
        <i class="ti ti-users"></i> {{ __('Blocked Leads') }}
    </a>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Fraud Shield') }}</li>
    <li class="breadcrumb-item active">{{ __('Manual Checker') }}</li>
@endsection

@section('content')
<div class="row g-4">
    {{-- Quota Card & Overview --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4" style="background: linear-gradient(135deg, #1e1b4b 0%, #4c1d95 60%, #7c3aed 100%); color: #fff;">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <span class="badge bg-white text-dark px-3 py-1 mb-2 rounded-pill font-weight-bold">
                        <i class="ti ti-shield-check text-primary"></i> 3-Source Live Verification
                    </span>
                    <h2 class="h3 font-weight-bold m-0 text-white">{{ __('Real-Time Customer Courier Data Checker') }}</h2>
                    <p class="text-white-50 m-0 mt-1">{{ __('BD Courier API + EliteMart + FraudChecker.link aggregated verification') }}</p>
                </div>
                <div class="text-md-end text-start">
                    <span class="d-block text-white-50 text-sm">{{ __('Today\'s Free Quota Usage') }}</span>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="h2 font-weight-bold m-0 text-warning" id="quota-display">{{ $usedToday }}</span>
                        <span class="text-white-50">/ {{ $dailyLimit }} {{ __('Checks') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search Input Box --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h4 class="card-title mb-3 font-weight-bold text-dark d-flex align-items-center gap-2">
                <i class="ti ti-search text-primary"></i> {{ __('Search Customer by Mobile Number') }}
            </h4>
            <div class="row g-3">
                <div class="col-md-8 col-12">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="ti ti-phone"></i>
                        </span>
                        <input type="tel" id="fraud-search-phone" class="form-control bg-light border-start-0" 
                               placeholder="e.g. 017XXXXXXXX" maxlength="11" autocomplete="off" />
                    </div>
                    <small class="text-muted mt-1 d-block">{{ __('Enter standard 11-digit Bangladesh phone number (013 - 019)') }}</small>
                </div>
                <div class="col-md-4 col-12 d-flex gap-2">
                    <button type="button" id="fraud-search-btn" class="btn btn-primary btn-lg flex-grow-1 d-flex align-items-center justify-content-center gap-2">
                        <i class="ti ti-search"></i> <span>{{ __('Check History') }}</span>
                    </button>
                    <button type="button" id="fraud-force-btn" class="btn btn-outline-secondary btn-lg" data-bs-toggle="tooltip" title="{{ __('Force Fresh Scrape (Bypass Cache)') }}">
                        <i class="ti ti-refresh"></i>
                    </button>
                </div>
            </div>

            {{-- Result Display Area --}}
            <div id="fraud-result-box" class="mt-4" style="display: none;">
                <hr class="my-4">
                <div class="p-3 rounded-3" id="fraud-status-card">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <span class="text-xs text-uppercase font-weight-bold text-muted">{{ __('Verified Number') }}</span>
                            <h3 class="h4 font-weight-bold m-0" id="res-phone">017XXXXXXXX</h3>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="position-relative" style="width: 70px; height: 70px;">
                                <svg class="w-100 h-100" viewBox="0 0 36 36">
                                    <path stroke="#e2e8f0" stroke-width="3.5" fill="none"
                                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    <path id="res-circle-progress" stroke="#10b981" stroke-width="3.5" stroke-dasharray="100, 100" stroke-linecap="round" fill="none"
                                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle text-center" style="transform: translate(-50%, -50%);">
                                    <span class="h6 font-weight-bold m-0 d-block" id="res-rate-percent">0%</span>
                                </div>
                            </div>
                            <div>
                                <span class="d-block text-xs font-weight-bold text-muted text-uppercase">{{ __('Courier Delivery Rate') }}</span>
                                <span class="badge px-3 py-2 rounded-pill font-weight-bold h6 m-0" id="res-rate-badge">Safe</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 text-center mb-3">
                        <div class="col-md-4 col-4">
                            <div class="bg-white p-3 rounded-3 border">
                                <span class="d-block text-muted text-xs">{{ __('Total Orders') }}</span>
                                <span class="h3 font-weight-bold text-dark m-0" id="res-orders">0</span>
                            </div>
                        </div>
                        <div class="col-md-4 col-4">
                            <div class="bg-white p-3 rounded-3 border">
                                <span class="d-block text-muted text-xs">{{ __('Delivered') }}</span>
                                <span class="h3 font-weight-bold text-success m-0" id="res-delivery">0</span>
                            </div>
                        </div>
                        <div class="col-md-4 col-4">
                            <div class="bg-white p-3 rounded-3 border">
                                <span class="d-block text-muted text-xs">{{ __('Cancelled / Returned') }}</span>
                                <span class="h3 font-weight-bold text-danger m-0" id="res-cancel">0</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-3 rounded-3 border">
                        <span class="d-block text-muted text-xs mb-1">{{ __('Courier Breakdown') }}</span>
                        <p class="m-0 font-weight-bold text-dark" id="res-courier">-</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Verification Logs --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h4 class="card-title mb-3 font-weight-bold text-dark d-flex align-items-center gap-2">
                <i class="ti ti-history text-primary"></i> {{ __('Recent Verification Logs') }}
            </h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Date & Time') }}</th>
                            <th>{{ __('Phone Number') }}</th>
                            <th>{{ __('Courier Breakdown') }}</th>
                            <th>{{ __('Total Orders') }}</th>
                            <th>{{ __('Delivered') }}</th>
                            <th>{{ __('Cancelled') }}</th>
                            <th>{{ __('Success Rate') }}</th>
                            <th>{{ __('Check Type') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody id="recent-logs-body">
                        @forelse($recentLogs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                <td class="font-weight-bold">{{ $log->phone }}</td>
                                <td>{{ $log->courier_details ?: '-' }}</td>
                                <td>{{ $log->total_orders }}</td>
                                <td class="text-success font-weight-bold">{{ $log->total_delivery }}</td>
                                <td class="text-danger font-weight-bold">{{ $log->total_cancel }}</td>
                                <td>
                                    @if($log->success_rate >= 80)
                                        <span class="badge bg-success">{{ $log->success_rate }}%</span>
                                    @elseif($log->success_rate >= 50)
                                        <span class="badge bg-warning text-dark">{{ $log->success_rate }}%</span>
                                    @else
                                        <span class="badge bg-danger">{{ $log->success_rate }}%</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $log->check_type === 'live_checkout' ? __('Checkout Auto') : __('Manual Admin') }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->is_blocked)
                                        <span class="badge bg-danger">{{ __('BLOCKED') }}</span>
                                    @else
                                        <span class="badge bg-success">{{ __('ALLOWED') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">{{ __('No verification logs recorded yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('page-style')
<script>
window.onTurboLoad(function () {
    const searchBtn = document.getElementById('fraud-search-btn');
    const forceBtn = document.getElementById('fraud-force-btn');
    const phoneInput = document.getElementById('fraud-search-phone');
    const resultBox = document.getElementById('fraud-result-box');

    if (!searchBtn || !phoneInput) return;

    function doSearch(forceRefresh = false) {
        const phone = phoneInput.value.trim();
        if (phone.length !== 11) {
            alert('১১ ডিজিটের সঠিক মোবাইল নম্বর লিখুন (e.g. 017XXXXXXXX)');
            phoneInput.focus();
            return;
        }

        searchBtn.disabled = true;
        searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Checking...';

        fetch("{{ route('fraud.shield.query') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                phone: phone,
                force_refresh: forceRefresh
            })
        })
        .then(res => res.json())
        .then(data => {
            searchBtn.disabled = false;
            searchBtn.innerHTML = '<i class="ti ti-search"></i> <span>Check History</span>';

            if (data.status === 'quota_exceeded') {
                alert(data.message);
                return;
            }

            if (data.status !== 'success' || !data.data) {
                alert(data.message || 'ডেটা লোড করা সম্ভব হয়নি');
                return;
            }

            const item = data.data;
            document.getElementById('quota-display').innerText = data.used_today;

            document.getElementById('res-phone').innerText = item.phone;
            document.getElementById('res-orders').innerText = item.total_orders;
            document.getElementById('res-delivery').innerText = item.total_delivery;
            document.getElementById('res-cancel').innerText = item.total_cancel;
            document.getElementById('res-courier').innerText = item.courier_details || '-';

            const rateBadge = document.getElementById('res-rate-badge');
            const ratePercent = document.getElementById('res-rate-percent');
            const circleProgress = document.getElementById('res-circle-progress');
            const statusCard = document.getElementById('fraud-status-card');

            const rate = Math.max(0, Math.min(100, parseInt(item.success_rate || 0)));
            ratePercent.innerText = rate + '%';
            circleProgress.setAttribute('stroke-dasharray', `${rate}, 100`);

            if (rate >= 80) {
                rateBadge.innerText = 'নিরাপদ কাস্টমার (' + rate + '%)';
                rateBadge.className = 'badge px-3 py-2 rounded-pill font-weight-bold h6 m-0 bg-success text-white';
                circleProgress.setAttribute('stroke', '#10b981');
                statusCard.style.backgroundColor = '#ecfdf5';
            } else if (rate >= 50) {
                rateBadge.innerText = 'মাঝারি ঝুঁকি (' + rate + '%)';
                rateBadge.className = 'badge px-3 py-2 rounded-pill font-weight-bold h6 m-0 bg-warning text-dark';
                circleProgress.setAttribute('stroke', '#f59e0b');
                statusCard.style.backgroundColor = '#fffbeb';
            } else {
                rateBadge.innerText = 'উচ্চ ঝুঁকি / ফ্রড (' + rate + '%)';
                rateBadge.className = 'badge px-3 py-2 rounded-pill font-weight-bold h6 m-0 bg-danger text-white';
                circleProgress.setAttribute('stroke', '#ef4444');
                statusCard.style.backgroundColor = '#fef2f2';
            }

            resultBox.style.display = 'block';
        })
        .catch(err => {
            searchBtn.disabled = false;
            searchBtn.innerHTML = '<i class="ti ti-search"></i> <span>Check History</span>';
            alert('সার্ভার এরর: ' + err.message);
        });
    }

    searchBtn.onclick = () => doSearch(false);
    forceBtn.onclick = () => doSearch(true);
    phoneInput.onkeydown = (e) => {
        if (e.key === 'Enter') doSearch(false);
    };
});
</script>
@endpush
@endsection
