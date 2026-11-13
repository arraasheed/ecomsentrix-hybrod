@extends('receiptengine::templates.layout')

@section('receipt')
<header style="background:#172033;color:#fff;padding:26px;border-radius:8px;display:flex;justify-content:space-between;gap:20px;">
    <div>@if ($receipt['store']['logo']) <img class="brand-logo" src="{{ $receipt['store']['logo'] }}" alt="{{ $receipt['store']['name'] }}"> @endif<h1 style="margin:12px 0 0;font-size:26px;">{{ $receipt['store']['name'] }}</h1><div>{{ $receipt['store']['phone'] }} · {{ $receipt['store']['address'] }}</div></div>
    <div class="text-right"><div>{{ __('INVOICE') }}</div><strong style="font-size:22px;">{{ $receipt['order']['id'] }}</strong><br>{{ $receipt['order']['date'] }}</div>
</header>
<div class="receipt-grid" style="margin-top:28px;background:#f8fafc;padding:18px;border-radius:8px;">
    <div><strong>{{ __('Customer') }}</strong><br>{{ $receipt['customer']['name'] }}<br>{{ $receipt['customer']['phone'] }}<br>{{ $receipt['customer']['address'] }}</div>
    <div class="text-right"><strong>{{ __('Payment details') }}</strong><br>{{ $receipt['order']['payment_type'] }}<br>{{ __('Status') }}: {{ $receipt['order']['status'] }}</div>
</div>
<table class="items">
    <thead style="background:#eef2f6;"><tr><th>{{ __('Description') }}</th><th>{{ __('Quantity') }}</th><th>{{ __('Line total') }}</th></tr></thead>
    <tbody>@foreach ($receipt['items'] as $item)<tr><td>{{ $item['name'] }}</td><td>{{ $item['quantity'] }}</td><td>{{ $receipt['currency'] }} {{ number_format($item['total'], 2) }}</td></tr>@endforeach</tbody>
</table>
<div class="summary"><div><span>{{ __('Subtotal') }}</span><span>{{ $receipt['currency'] }} {{ number_format($receipt['subtotal'], 2) }}</span></div><div><span>{{ __('Delivery charge') }}</span><span>{{ $receipt['currency'] }} {{ number_format($receipt['delivery'], 2) }}</span></div><div><span>{{ __('Discount') }}</span><span>- {{ $receipt['currency'] }} {{ number_format($receipt['discount'], 2) }}</span></div><div class="total"><span>{{ __('Grand total') }}</span><span>{{ $receipt['currency'] }} {{ number_format($receipt['total'], 2) }}</span></div></div>
<p class="muted" style="margin-top:40px;">{{ __('Thank you for shopping with us.') }}</p>
@endsection
