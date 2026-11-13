@extends('receiptengine::templates.layout')

@section('receipt')
<style>
    .thermal { width:80mm; max-width:100%; margin:auto; font-family:monospace; font-size:12px; }
    .thermal h1 { font-size:18px; text-align:center; margin:4px 0; }
    .thermal hr { border:0; border-top:1px dashed #111; }
    .thermal table { width:100%; border-collapse:collapse; }
    .thermal td:last-child, .thermal th:last-child { text-align:right; }
    .thermal td, .thermal th { padding:4px 0; }
    @media print { .thermal { width:80mm; } }
</style>
<div class="thermal">
    @if ($receipt['store']['logo']) <img src="{{ $receipt['store']['logo'] }}" alt="{{ $receipt['store']['name'] }}" style="display:block;max-width:42mm;max-height:18mm;margin:0 auto 5px;"> @endif
    <h1>{{ $receipt['store']['name'] }}</h1>
    <div style="text-align:center;">{{ $receipt['store']['phone'] }}<br>{{ $receipt['store']['address'] }}</div>
    <hr><div>{{ $receipt['order']['id'] }} · {{ $receipt['order']['date'] }}</div><div>{{ $receipt['customer']['name'] }}</div><hr>
    <table><thead><tr><th style="text-align:left;">{{ __('Item') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Total') }}</th></tr></thead><tbody>@foreach ($receipt['items'] as $item)<tr><td>{{ \Illuminate\Support\Str::limit($item['name'], 22) }}</td><td>{{ $item['quantity'] }}</td><td>{{ $receipt['currency'] }}{{ number_format($item['total'], 0) }}</td></tr>@endforeach</tbody></table>
    <hr><div style="display:flex;justify-content:space-between;"><span>{{ __('Subtotal') }}</span><span>{{ $receipt['currency'] }}{{ number_format($receipt['subtotal'], 2) }}</span></div><div style="display:flex;justify-content:space-between;"><span>{{ __('Delivery') }}</span><span>{{ $receipt['currency'] }}{{ number_format($receipt['delivery'], 2) }}</span></div><div style="display:flex;justify-content:space-between;"><span>{{ __('Discount') }}</span><span>-{{ $receipt['currency'] }}{{ number_format($receipt['discount'], 2) }}</span></div><div style="display:flex;justify-content:space-between;font-size:16px;font-weight:bold;"><span>{{ __('TOTAL') }}</span><span>{{ $receipt['currency'] }}{{ number_format($receipt['total'], 2) }}</span></div><hr><div style="text-align:center;">{{ __('Thank you!') }}</div>
</div>
@endsection
