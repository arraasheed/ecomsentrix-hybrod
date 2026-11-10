@extends('layouts.app')

@section('page-title')
    {{ __('Manual Fraud Checker') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Fraud Shield') }}</li>
    <li class="breadcrumb-item">{{ __('Manual Checker') }}</li>
@endsection

@push('css')
    <style>
        .royal-gradient-card {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white;
            border: none;
        }
        .stat-badge {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
            border-radius: 50rem;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card royal-gradient-card shadow-lg mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="text-white mb-2"><i class="ti ti-shield-check"></i> {{ __('BD Real-time Fraud Engine') }}</h3>
                            <p class="text-white text-opacity-75 mb-0">
                                {{ __('Check any Bangladeshi phone number for courier delivery history across multiple sources.') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="badge bg-white bg-opacity-25 p-2 rounded">
                                <span class="d-block text-white-50 small">{{ __('Daily Limit Used') }}</span>
                                <h4 class="text-white mb-0" id="usage-stats">
                                    {{ $usedToday }} / {{ $dailyLimit }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mx-auto">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <label class="form-label font-bold text-dark fs-5">{{ __('Enter Bangladeshi Phone Number') }}</label>
                        <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden">
                            <span class="input-group-text bg-white border-0 ps-4"><i class="ti ti-phone text-muted"></i></span>
                            <input type="tel" id="phone-input" class="form-control border-0 py-3" placeholder="01XXXXXXXXX" maxlength="11">
                            <button class="btn btn-primary px-4 border-0" type="button" id="btn-check" style="background: #6366f1;">
                                {{ __('Check Now') }}
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block">{{ __('Standard 11-digit mobile number starting with 01') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 mx-auto mt-4" id="result-container" style="display: none;">
            <!-- Result will be injected here via JS -->
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#btn-check').click(function() {
                const phone = $('#phone-input').val();
                if (!phone || phone.length < 11) {
                    show_toastr('Error', 'Please enter a valid 11-digit phone number', 'error');
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Checking...');
                $('#result-container').fadeOut();

                $.ajax({
                    url: '{{ route("admin.fraud.manual-query") }}',
                    type: 'POST',
                    data: {
                        phone: phone,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        btn.prop('disabled', false).html('Check Now');
                        
                        if (res.status === 'success') {
                            const data = res.data;
                            $('#usage-stats').text(`${res.used_today} / ${res.daily_limit}`);
                            
                            let deliveryRate = 0;
                            if (data.total_parcel > 0) {
                                deliveryRate = (data.success_parcel / data.total_parcel) * 100;
                            }

                            let riskColor = 'success';
                            let riskText = 'SAFE CUSTOMER';
                            if (deliveryRate < 50) {
                                riskColor = 'danger';
                                riskText = 'HIGH RISK FRUD';
                            } else if (deliveryRate < 80) {
                                riskColor = 'warning';
                                riskText = 'MEDIUM RISK';
                            }

                            let html = `
                                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                                    <div class="card-header border-0 bg-${riskColor} bg-opacity-10 py-3 text-center">
                                        <h5 class="mb-0 text-${riskColor}">${riskText} - ${deliveryRate.toFixed(2)}% Success Rate</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row text-center g-3">
                                            <div class="col-4">
                                                <div class="p-3 bg-light rounded-4">
                                                    <h4 class="mb-0 fw-bold">${data.total_parcel}</h4>
                                                    <small class="text-muted">Total Orders</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="p-3 bg-success bg-opacity-10 rounded-4">
                                                    <h4 class="mb-0 fw-bold text-success">${data.success_parcel}</h4>
                                                    <small class="text-success">Delivered</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="p-3 bg-danger bg-opacity-10 rounded-4">
                                                    <h4 class="mb-0 fw-bold text-danger">${data.cancelled_parcel}</h4>
                                                    <small class="text-danger">Returned</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            $('#result-container').html(html).fadeIn();
                        } else {
                            show_toastr('Error', res.message || 'Check failed', 'error');
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('Check Now');
                        show_toastr('Error', 'Server connection error', 'error');
                    }
                });
            });

            $('#phone-input').keypress(function(e) {
                if (e.which == 13) $('#btn-check').click();
            });
        });
    </script>
@endpush
