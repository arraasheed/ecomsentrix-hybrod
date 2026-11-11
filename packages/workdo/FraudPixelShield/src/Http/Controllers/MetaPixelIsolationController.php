<?php

namespace Workdo\FraudPixelShield\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Store;
use App\Models\Order;
use App\Models\Utility;
use Workdo\FraudPixelShield\Models\CapiDispatchLog;
use Workdo\FraudPixelShield\Jobs\SendMetaCapiPurchaseJob;

class MetaPixelIsolationController extends Controller
{
    /**
     * Helper to get active store
     */
    protected function getActiveStore()
    {
        $storeId = getCurrentStore();
        return Store::find($storeId) ?: (Auth::user() ? Auth::user()->currentStore : null);
    }

    /**
     * Pixel Isolation Dashboard
     */
    public function dashboard(Request $request)
    {
        $store = $this->getActiveStore();
        if (!$store) {
            return redirect()->back()->with('error', 'Store not found.');
        }

        // Metrics
        $totalHeld = Order::where('store_id', $store->id)
            ->where(function ($q) {
                $q->where('pixel_status', 'hold')
                  ->orWhereNull('pixel_status');
            })->count();

        $totalDispatched = Order::where('store_id', $store->id)
            ->where('pixel_status', 'dispatched')->count();

        $pendingConfirm = Order::where('store_id', $store->id)
            ->where(function ($q) {
                $q->where('capi_status', 'pending')
                  ->orWhereNull('capi_status');
            })->count();

        // Estimated Ad Spend Saved (৳120 estimated bad CPA saved per held order)
        $estimatedSaved = $totalHeld * 120;

        $pixelEnabled = Utility::GetValueByName('pixel_isolation_enabled', $store->id);
        $isIsolationActive = ($pixelEnabled !== 'off'); // Default on

        $pixelId = Utility::GetValueByName('meta_pixel_id', $store->id);
        $capiToken = Utility::GetValueByName('meta_capi_access_token', $store->id);
        $delayUntil = Utility::GetValueByName('delay_capi_until', $store->id) ?: 'order_confirm';

        // Orders list for merchant
        $orders = Order::where('store_id', $store->id)
            ->latest()
            ->paginate(15);

        return view('fraud-pixel-shield::admin.pixel-dashboard', compact(
            'store',
            'totalHeld',
            'totalDispatched',
            'pendingConfirm',
            'estimatedSaved',
            'isIsolationActive',
            'pixelId',
            'capiToken',
            'delayUntil',
            'orders'
        ));
    }

    /**
     * Helper to update setting and clear cache
     */
    protected function updateSetting($name, $value, $storeId)
    {
        \App\Models\Setting::updateOrCreate(
            ['name' => $name, 'store_id' => $storeId],
            ['value' => $value]
        );
        \Illuminate\Support\Facades\Cache::forget("settings_store_{$storeId}");
    }

    /**
     * Toggle Pixel Isolation On/Off
     */
    public function toggleStatus(Request $request)
    {
        $store = $this->getActiveStore();
        if (!$store) {
            return response()->json(['status' => 'error', 'message' => 'Store not found'], 404);
        }

        $current = Utility::GetValueByName('pixel_isolation_enabled', $store->id);
        $newStatus = ($current === 'off') ? 'on' : 'off';

        $this->updateSetting('pixel_isolation_enabled', $newStatus, $store->id);

        return response()->json([
            'status' => 'success',
            'enabled' => ($newStatus === 'on'),
            'message' => ($newStatus === 'on') 
                ? 'Pixel Isolation সক্রিয় করা হয়েছে (Purchase হোল্ড থাকবে)' 
                : 'Pixel Isolation নিষ্ক্রিয় করা হয়েছে (সরাসরি পিক্সেল ফায়ার হবে)'
        ]);
    }

    /**
     * Save/Update Meta Pixel & CAPI Settings
     */
    public function updateSettings(Request $request)
    {
        $store = $this->getActiveStore();
        if (!$store) {
            return redirect()->back()->with('error', 'Store not found.');
        }

        $validated = $request->validate([
            'meta_pixel_id' => 'nullable|string|max:100',
            'meta_capi_access_token' => 'nullable|string|max:500',
            'delay_capi_until' => 'required|in:order_confirm,delivery_confirm',
        ]);

        $this->updateSetting('meta_pixel_id', $validated['meta_pixel_id'] ?? '', $store->id);
        $this->updateSetting('meta_capi_access_token', $validated['meta_capi_access_token'] ?? '', $store->id);
        $this->updateSetting('delay_capi_until', $validated['delay_capi_until'], $store->id);

        if ($request->has('pixel_isolation_enabled')) {
            $this->updateSetting('pixel_isolation_enabled', $request->input('pixel_isolation_enabled'), $store->id);
        }

        return redirect()->back()->with('success', __('Meta Pixel & CAPI সেটিংস সফলভাবে সংরক্ষিত হয়েছে।'));
    }

    /**
     * Trigger CAPI Purchase Event on Merchant Confirmation
     */
    public function triggerCapi(Order $order)
    {
        $store = $this->getActiveStore();
        if (!$store) {
            return redirect()->back()->with('error', 'Store not found.');
        }

        // Dispatch Meta CAPI queue job
        SendMetaCapiPurchaseJob::dispatch($order, $store);

        // Update Order pixel and CAPI statuses
        $order->pixel_status = 'dispatched';
        $order->capi_status = 'dispatched';
        $order->save();

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => "Order #{$order->id} এর Purchase ইভেন্ট সফলভাবে মেটা CAPI-তে পাঠানো হয়েছে।"
            ]);
        }

        return redirect()->back()->with('success', "Order #{$order->id} এর Purchase ইভেন্ট সফলভাবে মেটা CAPI-তে পাঠানো হয়েছে।");
    }

    /**
     * View CAPI Logs
     */
    public function capiLogs()
    {
        $store = $this->getActiveStore();
        if (!$store) {
            return redirect()->back()->with('error', 'Store not found.');
        }

        $logs = CapiDispatchLog::where('store_id', $store->id)
            ->with('order')
            ->latest()
            ->paginate(20);

        return view('fraud-pixel-shield::admin.capi-logs', compact('store', 'logs'));
    }

    /**
     * Retry CAPI log
     */
    public function retryCapi(CapiDispatchLog $log)
    {
        $store = $this->getActiveStore();
        if (!$store || $log->store_id !== $store->id) {
            abort(403, 'Unauthorized');
        }

        if ($log->order) {
            SendMetaCapiPurchaseJob::dispatch($log->order, $store);
            $log->order->pixel_status = 'dispatched';
            $log->order->capi_status = 'dispatched';
            $log->order->save();

            return redirect()->back()->with('success', 'Meta CAPI Purchase event re-queued for dispatch.');
        }

        return redirect()->back()->with('error', 'Order not found for this log.');
    }
}
