@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Central Pixel Blocks</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('central.index') }}">Central Intelligence</a></li>
                        <li class="breadcrumb-item active">Pixel Blocks</li>
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
                            <label class="form-label">Search Order ID</label>
                            <input type="text" name="order_id" class="form-control" value="{{ request('order_id') }}" placeholder="Enter order ID">
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
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="dispatched" {{ request('status') === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2"><i class="ri-search-line me-1"></i> Search</button>
                            <a href="{{ route('central.pixel-blocks.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                                    <th>Order ID</th>
                                    <th>Store</th>
                                    <th>Customer Phone</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Retry Count</th>
                                    <th>Last Error</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pixelBlocks as $block)
                                <tr>
                                    <td>{{ $block->id }}</td>
                                    <td>{{ $block->order_id }}</td>
                                    <td>{{ $block->store->name ?? 'N/A' }}</td>
                                    <td>{{ $block->customer_phone }}</td>
                                    <td>{{ number_format($block->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $block->status === 'dispatched' ? 'success' : ($block->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($block->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $block->retry_count }}</td>
                                    <td>{{ $block->last_error ? Str::limit($block->last_error, 50) : '-' }}</td>
                                    <td>{{ $block->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">No pixel blocks found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        {{ $pixelBlocks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection