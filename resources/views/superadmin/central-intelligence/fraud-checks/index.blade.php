@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Central Fraud Checks</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('central.index') }}">Central Intelligence</a></li>
                        <li class="breadcrumb-item active">Fraud Checks</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3 mb-4" id="filterForm">
                        <div class="col-md-3">
                            <label class="form-label">Search Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ request('phone') }}" placeholder="Enter phone number">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Store</label>
                            <select name="store_id" class="form-select">
                                <option value="">All Stores</option>
                                @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Risk Level</label>
                            <select name="risk" class="form-select">
                                <option value="">All Levels</option>
                                <option value="high" {{ request('risk') === 'high' ? 'selected' : '' }}>High Risk</option>
                                <option value="medium" {{ request('risk') === 'medium' ? 'selected' : '' }}>Medium Risk</option>
                                <option value="low" {{ request('risk') === 'low' ? 'selected' : '' }}>Low Risk</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2"><i class="ri-search-line me-1"></i> Search</button>
                            <a href="{{ route('central.fraud-checks.index') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Phone</th>
                                    <th>Store</th>
                                    <th>Total Orders</th>
                                    <th>Delivered</th>
                                    <th>Returned</th>
                                    <th>Return Rate</th>
                                    <th>Risk Level</th>
                                    <th>Source</th>
                                    <th>Checked At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fraudChecks as $check)
                                <tr>
                                    <td>{{ $check->id }}</td>
                                    <td>{{ $check->phone }}</td>
                                    <td>{{ $check->store->name ?? 'N/A' }}</td>
                                    <td>{{ $check->total_orders }}</td>
                                    <td>{{ $check->delivered_orders }}</td>
                                    <td>{{ $check->returned_orders }}</td>
                                    <td>
                                        <span class="badge bg-{{ $check->return_rate >= 50 ? 'danger' : ($check->return_rate >= 30 ? 'warning' : 'success') }}">
                                            {{ number_format($check->return_rate, 2) }}%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $check->risk_level === 'high' ? 'danger' : ($check->risk_level === 'medium' ? 'warning' : 'success') }}">
                                            {{ ucfirst($check->risk_level) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($check->source) }}</td>
                                    <td>{{ $check->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">No fraud checks found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        {{ $fraudChecks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection