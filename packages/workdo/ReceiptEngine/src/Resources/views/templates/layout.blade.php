<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $receipt['store']['name'] }} - {{ $receipt['order']['id'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f4f6; color: #172033; font: 14px/1.5 Arial, sans-serif; }
        .receipt-shell { max-width: 860px; margin: 32px auto; background: #fff; padding: 38px; }
        .receipt-toolbar { display: flex; justify-content: flex-end; gap: 8px; margin-bottom: 16px; }
        .receipt-toolbar a, .receipt-toolbar button { border: 0; border-radius: 6px; background: #172033; color: #fff; cursor: pointer; padding: 9px 14px; text-decoration: none; }
        .brand-logo { max-width: 150px; max-height: 60px; object-fit: contain; }
        .receipt-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .muted { color: #667085; }
        .text-right { text-align: right; }
        .items { width: 100%; margin-top: 28px; border-collapse: collapse; }
        .items th, .items td { padding: 11px 8px; text-align: left; }
        .items th:last-child, .items td:last-child { text-align: right; }
        .summary { width: 330px; margin: 20px 0 0 auto; }
        .summary div { display: flex; justify-content: space-between; padding: 5px 0; }
        .summary .total { border-top: 2px solid currentColor; font-size: 18px; font-weight: 700; margin-top: 8px; padding-top: 10px; }
        .terms { margin-top: 30px; border-radius: 8px; background: #f8fafc; padding: 14px; }
        .qr { width: 104px; height: 104px; object-fit: contain; }
        @media print { body { background: #fff; } .receipt-shell { margin: 0; max-width: none; padding: 0; } .receipt-toolbar { display: none; } }
        @media (max-width: 600px) { .receipt-shell { margin: 0; padding: 20px; } .receipt-grid { grid-template-columns: 1fr; } .summary { width: 100%; } }
    </style>
</head>
<body>
    <main class="receipt-shell">
        <nav class="receipt-toolbar">
            <button type="button" onclick="window.print()">{{ __('Print / Save PDF') }}</button>
            <a href="{{ url()->previous() }}">{{ __('Back') }}</a>
        </nav>
        @yield('receipt')
    </main>
    @if ($autoPrint)
        <script>window.addEventListener('load', () => window.print(), { once: true });</script>
    @endif
</body>
</html>
