@extends('layouts.app')

@section('page-title')
    {{ __('Blocked Fraud Leads') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Fraud Shield') }}</li>
    <li class="breadcrumb-item">{{ __('Blocked Leads') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header border-0 bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0 fw-bold">{{ __('Blocked Fraud Leads') }}</h5>
                            <small class="text-muted">{{ __('Customers who were blocked due to low delivery rate or blacklisted status.') }}</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">{{ __('Customer Name') }}</th>
                                    <th>{{ __('Phone Number') }}</th>
                                    <th>{{ __('Reason / History') }}</th>
                                    <th>{{ __('Cart Value') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="text-end pe-4">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leads as $lead)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $lead->customer_name }}</div>
                                            <small class="text-muted">{{ $lead->ip_address }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $lead->customer_phone }}</span>
                                                <a href="tel:{{ $lead->customer_phone }}" class="btn btn-sm btn-icon btn-outline-primary rounded-circle" title="Call Now">
                                                    <i class="ti ti-phone fs-6"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $stats = json_decode($lead->fraud_stats, true);
                                                $rate = 0;
                                                if($stats && $stats['total_parcel'] > 0) {
                                                    $rate = ($stats['success_parcel'] / $stats['total_parcel']) * 100;
                                                }
                                            @endphp
                                            <div class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">
                                                {{ $rate > 0 ? number_format($rate, 2) . '%' : '0%' }} {{ __('Success Rate') }}
                                            </div>
                                            <div class="small text-muted mt-1">
                                                {{ __('Total:') }} {{ $stats['total_parcel'] ?? 0 }}, 
                                                {{ __('Success:') }} {{ $stats['success_parcel'] ?? 0 }}, 
                                                {{ __('Failed:') }} {{ $stats['cancelled_parcel'] ?? 0 }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $lead->order_amount }}</span>
                                        </td>
                                        <td>
                                            <div class="small">{{ $lead->created_at->format('d M, Y') }}</div>
                                            <div class="text-muted smaller">{{ $lead->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    {{ __('Actions') }}
                                                </button>
                                                <ul class="dropdown-menu shadow border-0">
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center" href="https://wa.me/88{{ $lead->customer_phone }}" target="_blank">
                                                            <i class="ti ti-brand-whatsapp text-success me-2"></i> {{ __('WhatsApp Chat') }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <img src="{{ asset('assets/images/no-data.svg') }}" style="width: 150px;" class="mb-3">
                                            <h6 class="text-muted">{{ __('No blocked leads found yet.') }}</h6>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($leads->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        {{ $leads->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
