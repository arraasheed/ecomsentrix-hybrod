<?php

namespace Workdo\ReceiptEngine\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Workdo\ReceiptEngine\Services\ReceiptDataService;

class ReceiptController extends Controller
{
    public function show(Request $request, string $order, ReceiptDataService $receipts): View
    {
        $orderId = Crypt::decryptString($order);
        $record = Order::findOrFail($orderId);
        $this->authorizeOrder($request, $record);

        $template = $request->query('template', config('receiptengine.default_template'));
        abort_unless(in_array($template, config('receiptengine.templates'), true), 404);

        return view("receiptengine::templates.{$template}", [
            'receipt' => $receipts->forOrder($record),
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        if ($request->user() && $request->user()->type === 'admin') {
            abort_unless((int) $order->store_id === (int) getCurrentStore(), 404);
            return;
        }

        $customer = $request->user('customers');
        abort_unless($customer && (int) $order->customer_id === (int) $customer->id && !(bool) $order->is_guest, 404);
    }
}
