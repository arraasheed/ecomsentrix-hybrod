@extends('receiptengine::templates.layout')

@section('receipt')
<header style="display:flex;justify-content:space-between;gap:20px;align-items:flex-start;">
    <div>
        @if ($receipt['store']['logo']) <img class="brand-logo" src="{{ $receipt['store']['logo'] }}" alt="{{ $receipt['store']['name'] }}"> @endif
        <h1 style="font-size:26px;margin:12px 0 2px;">{{ $receipt['store']['name'] }}</h1>
        <div class="muted">{{ $receipt['store']['phone'] }}</div>
        <div class="muted">{{ $receipt['store']['address'] }}</div>
    </div>
    <div class="text-right">
        <div class="muted">{{ __('Receipt') }}</div>
        <h2 style="margin:3px 0;">{{ $receipt['order']['id'] }}</h2>
        <div class="muted">{{ $receipt['order']['date'] }}</div>
    </div>
</header>
<div class="receipt-grid" style="margin-top:42px;">
    <div><strong>{{ __('Billed to') }}</strong><br>{{ $receipt['customer']['name'] }}<br>{{ $receipt['customer']['phone'] }}<br>{{ $receipt['customer']['address'] }}</div>
    <div class="text-right"><strong>{{ __('Payment') }}</strong><br>{{ $receipt['order']['payment_type'] }}<br>{{ $receipt['order']['payment_status'] }}</div>
</div>
<table class="items">
    <thead><tr><th>{{ __('Item') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Amount') }}</th></tr></thead>
    <tbody>@foreach ($receipt['items'] as $item)<tr><td>{{ $item['name'] }}</td><td>{{ $item['quantity'] }}</td><td>{{ $receipt['currency'] }} {{ number_format($item['total'], 2) }}</td></tr>@endforeach</tbody>
</table>
<div class="summary">
    <div><span>{{ __('Subtotal') }}</span><span>{{ $receipt['currency'] }} {{ number_format($receipt['subtotal'], 2) }}</span></div>
    <div><span>{{ __('Delivery') }}</span><span>{{ $receipt['currency'] }} {{ number_format($receipt['delivery'], 2) }}</span></div>
    <div><span>{{ __('Discount') }}</span><span>- {{ $receipt['currency'] }} {{ number_format($receipt['discount'], 2) }}</span></div>
    <div class="total"><span>{{ __('Total') }}</span><span>{{ $receipt['currency'] }} {{ number_format($receipt['total'], 2) }}</span></div>
</div>
@endsection
