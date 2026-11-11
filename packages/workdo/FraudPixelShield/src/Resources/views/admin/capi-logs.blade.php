@extends('layouts.app')

@section('page-title')
    {{ __('Meta CAPI Dispatch Logs') }}
@endsection

@section('action-button')
    <a href="{{ route('fraud.pixel.dashboard') }}" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
        <i class="ti ti-arrow-left"></i> {{ __('Back to Dashboard') }}
    </a>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('fraud.pixel.dashboard') }}">{{ __('Pixel Isolation') }}</a></li>
    <li class="breadcrumb-item active">{{ __('CAPI Logs') }}</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-title m-0 font-weight-bold text-dark d-flex align-items-center gap-2">
                    <i class="ti ti-history text-primary"></i> {{ __('Server-Side CAPI Dispatch Ledger') }}
                </h4>
            </div>

            @if ($logs->isEmpty())
                <div class="text-center py-5">
                    <i class="ti ti-inbox text-muted" style="font-size: 48px;"></i>
                    <h5 class="text-muted mt-3">{{ __('কোনো মেটা CAPI ডিসপ্যাচ লগ পাওয়া যায়নি।') }}</h5>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('Order ID') }}</th>
                                <th>{{ __('Event') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Response Code') }}</th>
                                <th>{{ __('Dispatched At') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td class="font-weight-bold">
                                        @if ($log->order)
                                            <a href="{{ route('order.view', \Crypt::encrypt($log->order->id)) }}" class="text-primary text-decoration-none">
                                                #{{ $log->order->id }}
                                            </a>
                                        @else
                                            <span class="text-muted">#{{ $log->order_id }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border px-2 py-1">{{ $log->event_name }}</span>
                                    </td>
                                    <td>
                                        @if ($log->status === 'success' || $log->status === 'sent')
                                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                                <i class="ti ti-circle-check"></i> {{ __('Success') }}
                                            </span>
                                        @elseif ($log->status === 'failed')
                                            <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                                <i class="ti ti-alert-triangle"></i> {{ __('Failed') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">{{ $log->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ ($log->response_payload['events_received'] ?? null) ? 'bg-success' : 'bg-light text-dark border' }}">
                                            {{ ($log->response_payload['events_received'] ?? null) ? '200 OK' : 'Logged' }}
                                        </span>
                                    </td>
                                    <td>{{ $log->created_at ? $log->created_at->format('d M Y, h:i A') : '-' }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            @if ($log->status === 'failed' && $log->order)
                                                <form action="{{ route('fraud.pixel.capi.retry', $log->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-primary" title="{{ __('Retry') }}">
                                                        <i class="ti ti-refresh"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailModal{{ $log->id }}" title="{{ __('View Details') }}">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                        </div>

                                        {{-- Detail Modal --}}
                                        <div class="modal fade" id="detailModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content text-start">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title font-weight-bold">
                                                            <i class="ti ti-code"></i> {{ __('CAPI Payload & Response') }} - Log #{{ $log->id }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <label class="form-label font-weight-bold">{{ __('Meta Response Payload:') }}</label>
                                                        <pre class="bg-dark text-white p-3 rounded-3" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode($log->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
