@extends('layouts.app')

@section('page-title', __('Blocked Fraud Leads'))

@section('action-button')
    <a href="{{ route('fraud.shield.manual.checker') }}" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
        <i class="ti ti-search"></i> {{ __('Manual Checker') }}
    </a>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('fraud.shield.manual.checker') }}">{{ __('Fraud Shield') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Blocked Leads') }}</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <h4 class="card-title font-weight-bold text-dark m-0 d-flex align-items-center gap-2">
                        <i class="ti ti-user-x text-danger"></i> {{ __('Blocked High-Risk Customer Leads') }}
                    </h4>
                    <p class="text-muted text-sm m-0 mt-1">{{ __('Customers blocked during live checkout who were offered 1-click WhatsApp order') }}</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Date & Time') }}</th>
                            <th>{{ __('Customer Name') }}</th>
                            <th>{{ __('Phone Number') }}</th>
                            <th>{{ __('Address') }}</th>
                            <th>{{ __('Cart Total') }}</th>
                            <th>{{ __('Courier Success Rate') }}</th>
                            <th>{{ __('Courier Breakdown') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                            <tr>
                                <td>{{ $lead->created_at->format('d M Y, h:i A') }}</td>
                                <td class="font-weight-bold">{{ $lead->name ?: 'Guest' }}</td>
                                <td>{{ $lead->phone }}</td>
                                <td><small class="text-muted">{{ Str::limit($lead->address, 40) ?: '-' }}</small></td>
                                <td class="font-weight-bold">৳{{ number_format($lead->cart_total, 2) }}</td>
                                <td>
                                    <span class="badge bg-danger">{{ $lead->success_rate }}%</span>
                                </td>
                                <td><small class="text-muted">{{ $lead->courier_details ?: '-' }}</small></td>
                                <td>
                                    @php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
                                        if (str_starts_with($cleanPhone, '0')) $cleanPhone = '88' . $cleanPhone;
                                        $waUrl = "https://wa.me/{$cleanPhone}?text=" . urlencode("হ্যালো " . ($lead->name ?: 'গ্রাহক') . "! আপনি আমাদের ওয়েবসাইটে অর্ডার করতে চেয়েছিলেন আমরা আপনার অর্ডারটি কনফার্ম করতে সহায়তা করতে পারি");
                                    @endphp
                                    <a href="{{ $waUrl }}" target="_blank" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1">
                                        <i class="ti ti-brand-whatsapp"></i> WhatsApp
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">{{ __('No blocked customer leads recorded.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $leads->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
