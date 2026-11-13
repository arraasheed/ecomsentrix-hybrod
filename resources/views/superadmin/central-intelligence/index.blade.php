@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Central Intelligence Dashboard</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item active">Central Intelligence</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-primary bg-opacity-10 rounded">
                                <i class="ri-shield-check-fill text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="text-muted mb-1">Total Fraud Checks</h5>
                            <h3 class="mb-0">{{ $totalFraudChecks }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-warning bg-opacity-10 rounded">
                                <i class="ri-shield-warning-line text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="text-muted mb-1">High Risk Detections</h5>
                            <h3 class="mb-0">{{ $highRiskDetections }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-info bg-opacity-10 rounded">
                                <i class="ri-shield-star-fill text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="text-muted mb-1">Total Pixel Blocks</h5>
                            <h3 class="mb-0">{{ $totalPixelBlocks }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Quick Actions</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('central.fraud-checks.index') }}" class="btn btn-primary w-100">
                                <i class="ri-search-line me-2"></i> Search Fraud Checks
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('central.pixel-blocks.index') }}" class="btn btn-info w-100">
                                <i class="ri-search-line me-2"></i> Search Pixel Blocks
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('central.fraud-checks.index') }}?risk=high" class="btn btn-danger w-100">
                                <i class="ri-alert-line me-2"></i> View High Risk Only
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('central.pixel-blocks.index') }}?status=pending" class="btn btn-warning w-100">
                                <i class="ri-time-line me-2"></i> Pending Pixel Blocks
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Recent Fraud Checks</h4>
                    <a href="{{ route('central.fraud-checks.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Phone</th>
                                    <th>Store</th>
                                    <th>Risk Level</th>
                                    <th>Checked At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentFraudChecks as $check)
                                <tr>
                                    <td>{{ $check->phone }}</td>
                                    <td>{{ $check->store->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $check->risk_level === 'high' ? 'danger' : ($check->risk_level === 'medium' ? 'warning' : 'success') }}">
                                            {{ ucfirst($check->risk_level) }}
                                        </span>
                                    </td>
                                    <td>{{ $check->created_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Recent Pixel Blocks</h4>
                    <a href="{{ route('central.pixel-blocks.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Store</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPixelBlocks as $block)
                                <tr>
                                    <td>{{ $block->order_id }}</td>
                                    <td>{{ $block->store->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $block->status === 'dispatched' ? 'success' : ($block->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($block->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $block->created_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection