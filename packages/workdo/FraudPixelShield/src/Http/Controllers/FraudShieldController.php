<?php

namespace Workdo\FraudPixelShield\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Workdo\FraudPixelShield\Models\FraudApiKey;
use Workdo\FraudPixelShield\Models\FraudCheckLog;
use Workdo\FraudPixelShield\Models\FraudLeadLog;

class FraudShieldController extends Controller
{
    /**
     * Clean phone to standard 11 digits (01XXXXXXXXX)
     */
    protected function cleanPhone($phone)
    {
        $p = preg_replace('/[^0-9]/', '', (string) $phone);
        if (str_starts_with($p, '880')) {
            $p = substr($p, 2);
        } elseif (str_starts_with($p, '88')) {
            $p = substr($p, 2);
        }
        return $p;
    }

    /**
     * Live Checkout 3-Source Fraud Check
     * Endpoint: POST /{slug}/fraud-check
     */
    public function checkLiveCheckout(Request $request, $slug)
    {
        if (env('FRAUD_PIXEL_SHIELD_ENABLED', true) === false) {
            return response()->json(['status' => 'bypassed', 'blocked' => false]);
        }

        $rawPhone = $request->input('phone');
        $cleanPhone = $this->cleanPhone($rawPhone);

        if (strlen($cleanPhone) !== 11 || !preg_match('/^01[3-9]\d{8}$/', $cleanPhone)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid 11-digit phone number',
                'blocked' => false
            ], 422);
        }

        $store = Store::where('slug', $slug)->first();
        if (!$store) {
            return response()->json(['status' => 'error', 'message' => 'Store not found', 'blocked' => false], 404);
        }

        // Store settings
        $enabled = Utility::GetValueByName('fraud_check_enabled', $store->id);
        if ($enabled === 'off') {
            return response()->json(['status' => 'disabled_by_store', 'blocked' => false]);
        }

        $minSuccessRate = (int) Utility::GetValueByName('fraud_risk_threshold', $store->id) ?: 50;
        $dailyLimit = (int) Utility::GetValueByName('fraud_daily_limit', $store->id) ?: 20;
        $storeWhatsApp = Utility::GetValueByName('whatsapp_number', $store->id) 
            ?: (Utility::GetValueByName('company_telephone', $store->id) ?: '8801948821476');

        // Check & increment daily quota (Live + Manual combined)
        $today = now()->format('Y-m-d');
        $quotaCacheKey = "store_{$store->id}_fraud_quota_{$today}";
        $usedToday = (int) Cache::get($quotaCacheKey, 0);

        if ($usedToday >= $dailyLimit) {
            Log::info("FraudShield: Store #{$store->id} daily quota exceeded ({$usedToday}/{$dailyLimit}).");
            return response()->json([
                'status' => 'quota_exceeded',
                'blocked' => false,
                'used' => $usedToday,
                'limit' => $dailyLimit,
                'message' => 'Daily free check quota reached'
            ]);
        }

        // 1. Cross-Tenant Internal SaaS Intelligence Check (Instant 0-50ms lookup across ALL platform stores)
        $internalBlocked = FraudCheckLog::where('phone', $cleanPhone)
            ->where('is_blocked', true)
            ->latest()
            ->first();

        if (!$internalBlocked) {
            $internalBlocked = FraudLeadLog::where('phone', $cleanPhone)
                ->where('status', 'blocked_whatsapp_offered')
                ->latest()
                ->first();
        }

        if ($internalBlocked) {
            Log::info("FraudShield: {$cleanPhone} blocked by internal SaaS network (Store #{$store->id}).");

            $cleanWhatsApp = $this->cleanPhone($storeWhatsApp);
            if (!str_starts_with($cleanWhatsApp, '880')) {
                $cleanWhatsApp = '88' . $cleanWhatsApp;
            }
            $waMsg = "হ্যালো! আমি ওয়েবসাইট থেকে অর্ডার করতে চেয়েছিলাম\nআমার মোবাইল নম্বর: {$cleanPhone}\nদয়া করে আমার অর্ডারটি নিশ্চিত করুন";
            $whatsAppUrl = "https://wa.me/{$cleanWhatsApp}?text=" . urlencode($waMsg);

            // Record this check for current store
            try {
                FraudCheckLog::create([
                    'store_id' => $store->id,
                    'phone' => $cleanPhone,
                    'courier_details' => is_array($internalBlocked->courier_details) ? $internalBlocked->courier_details : ['summary' => (string) ($internalBlocked->courier_details ?? 'পূর্বে অন্য স্টোরে ফ্রড/বাতিল হিসেবে চিহ্নিত')],
                    'total_orders' => (int) ($internalBlocked->total_orders ?? 1),
                    'total_delivery' => (int) ($internalBlocked->total_delivery ?? 0),
                    'total_cancel' => (int) ($internalBlocked->total_cancel ?? 1),
                    'success_rate' => (int) ($internalBlocked->success_rate ?? 0),
                    'risk_status' => 'high',
                    'is_blocked' => true,
                    'check_type' => 'live_checkout',
                    'ip_address' => $request->ip(),
                ]);
            } catch (\Throwable $e) {}

            return response()->json([
                'status' => 'success',
                'phone' => $cleanPhone,
                'blocked' => true,
                'success_rate' => (int) ($internalBlocked->success_rate ?? 0),
                'risk_status' => 'high',
                'total_orders' => (int) ($internalBlocked->total_orders ?? 1),
                'total_delivery' => (int) ($internalBlocked->total_delivery ?? 0),
                'total_cancel' => (int) ($internalBlocked->total_cancel ?? 1),
                'courier_details' => $internalBlocked->courier_details ?? 'পূর্বে প্ল্যাটফর্মের অন্য স্টোরে অস্বাভাবিক রিটার্ন রেকর্ড',
                'threshold' => $minSuccessRate,
                'whatsapp_url' => $whatsAppUrl,
                'whatsapp_number' => $storeWhatsApp,
                'used_today' => $usedToday,
                'daily_limit' => $dailyLimit,
                'data_source' => 'internal_saas_network'
            ]);
        }

        // 2. Check Redis cache (72h for >=5 orders, 24h for <5 orders)
        $cacheKey = "fraud_cache_{$cleanPhone}";
        $cached = Cache::get($cacheKey);

        $courierData = null;
        if ($cached && is_array($cached)) {
            $courierData = $cached;
            $courierData['source'] = 'cache';
        } else {
            // Acquire rotating API key from pool if configured
            $rawApiKey = FraudApiKey::acquireKey('BD Courier', $store->id);
            // Prefix with store ID as expected by worker: STORE_<store_id>_<original_key>
            $apiKey = "STORE_{$store->id}_{$rawApiKey}";

            // Call 3-Source Node Worker (127.0.0.1:3005) with 4s hard timeout
            $workerUrl = env('FRAUD_WORKER_URL', 'http://127.0.0.1:3005');
            try {
                $response = Http::timeout(4.0)->get("{$workerUrl}/check", [
                    'phone' => $cleanPhone,
                    'api_key' => $apiKey
                ]);

                if ($response->successful()) {
                    $courierData = $response->json();
                    $courierData['source'] = '3_source_worker';

                    // Dynamic Redis TTL
                    $totalOrders = (int) ($courierData['total_orders'] ?? 0);
                    $ttl = ($totalOrders >= 5) ? (72 * 3600) : (24 * 3600);
                    Cache::put($cacheKey, $courierData, $ttl);
                }
            } catch (\Throwable $e) {
                Log::warning("FraudShield: Worker call failed for {$cleanPhone}: " . $e->getMessage());
                // Fallback: safe default so customer checkout is never permanently broken by external network
                $courierData = [
                    'status' => 'fallback',
                    'phone' => $cleanPhone,
                    'total_orders' => 0,
                    'total_delivery' => 0,
                    'total_cancel' => 0,
                    'success_rate' => 100,
                    'courier_details' => 'Verification pending',
                    'source' => 'fallback'
                ];
            }
        }

        // Increment combined daily usage
        Cache::put($quotaCacheKey, $usedToday + 1, now()->endOfDay());

        $totalOrders = (int) ($courierData['total_orders'] ?? 0);
        $totalDelivery = (int) ($courierData['total_delivery'] ?? 0);
        $totalCancel = (int) ($courierData['total_cancel'] ?? 0);
        $successRate = (int) ($courierData['success_rate'] ?? 0);
        $courierDetails = $courierData['courier_details'] ?? '-';

        // Decision logic:
        // Only block if customer has at least 1 previous recorded order and delivery success rate is below admin threshold
        $isBlocked = false;
        $riskStatus = 'safe';

        if ($totalOrders > 0) {
            if ($successRate < $minSuccessRate) {
                $isBlocked = true;
                $riskStatus = 'high';
            } elseif ($successRate < 75) {
                $riskStatus = 'medium';
            }
        }

        // Prepare WhatsApp message URL
        $cleanWhatsApp = $this->cleanPhone($storeWhatsApp);
        if (!str_starts_with($cleanWhatsApp, '880')) {
            $cleanWhatsApp = '88' . $cleanWhatsApp;
        }

        $waMsg = "হ্যালো! আমি ওয়েবসাইট থেকে অর্ডার করতে চেয়েছিলাম\nআমার মোবাইল নম্বর: {$cleanPhone}\nদয়া করে আমার অর্ডারটি নিশ্চিত করুন";
        $whatsAppUrl = "https://wa.me/{$cleanWhatsApp}?text=" . urlencode($waMsg);

        // Record check log in database
        try {
            FraudCheckLog::create([
                'store_id' => $store->id,
                'phone' => $cleanPhone,
                'courier_details' => is_array($courierDetails) ? $courierDetails : ['summary' => (string) $courierDetails],
                'total_orders' => $totalOrders,
                'total_delivery' => $totalDelivery,
                'total_cancel' => $totalCancel,
                'success_rate' => $successRate,
                'risk_status' => $riskStatus,
                'is_blocked' => $isBlocked,
                'check_type' => 'live_checkout',
                'ip_address' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::error("FraudShield: Failed to save check log: " . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'phone' => $cleanPhone,
            'blocked' => $isBlocked,
            'success_rate' => $successRate,
            'risk_status' => $riskStatus,
            'total_orders' => $totalOrders,
            'total_delivery' => $totalDelivery,
            'total_cancel' => $totalCancel,
            'courier_details' => $courierDetails,
            'threshold' => $minSuccessRate,
            'whatsapp_url' => $whatsAppUrl,
            'whatsapp_number' => $storeWhatsApp,
            'used_today' => $usedToday + 1,
            'daily_limit' => $dailyLimit,
            'data_source' => $courierData['source'] ?? 'live'
        ]);
    }

    /**
     * Capture lead when a high-risk order attempt is blocked
     * Endpoint: POST /{slug}/fraud-log-lead
     */
    public function logBlockedLead(Request $request, $slug)
    {
        $store = Store::where('slug', $slug)->first();
        if (!$store) {
            return response()->json(['status' => 'error'], 404);
        }

        $cleanPhone = $this->cleanPhone($request->input('phone'));

        try {
            $lead = FraudLeadLog::create([
                'store_id' => $store->id,
                'phone' => $cleanPhone,
                'name' => $request->input('name'),
                'address' => $request->input('address'),
                'cart_total' => (float) $request->input('cart_total', 0),
                'success_rate' => (int) $request->input('success_rate', 0),
                'courier_details' => is_array($request->input('courier_details')) ? $request->input('courier_details') : ['summary' => (string) $request->input('courier_details')],
                'ip_address' => $request->ip(),
                'status' => 'pending',
            ]);

            return response()->json(['status' => 'success', 'lead_id' => $lead->id]);
        } catch (\Throwable $e) {
            Log::error("FraudShield: Failed to save lead: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store Admin: Manual Fraud Checker View
     */
    public function manualChecker(Request $request)
    {
        $storeId = getCurrentStore();
        $store = Store::find($storeId);

        $today = now()->format('Y-m-d');
        $quotaKey = "store_{$storeId}_fraud_quota_{$today}";
        $usedToday = (int) Cache::get($quotaKey, 0);
        $dailyLimit = (int) Utility::GetValueByName('fraud_daily_limit', $storeId) ?: 20;

        $recentLogs = FraudCheckLog::where('store_id', $storeId)
            ->orderBy('id', 'desc')
            ->limit(15)
            ->get();

        return view('fraud-pixel-shield::admin.manual-checker', compact('store', 'usedToday', 'dailyLimit', 'recentLogs'));
    }

    /**
     * Display the list of blocked leads
     */
    public function leads()
    {
        $storeId = getCurrentStore();
        $leads = FraudLeadLog::where('store_id', $storeId)->orderBy('created_at', 'desc')->paginate(25);

        return view('fraud-pixel-shield::admin.leads', compact('leads'));
    }

    /**
     * Manual number query for admin dashboard
     */
    public function manualNumberQuery(Request $request)
    {
        $cleanPhone = $this->cleanPhone($request->input('phone'));
        if (strlen($cleanPhone) !== 11) {
            return response()->json(['status' => 'error', 'message' => '১১ ডিজিটের সঠিক মোবাইল নম্বর দিন'], 422);
        }

        $storeId = getCurrentStore();
        $store = Store::find($storeId);

        $today = now()->format('Y-m-d');
        $quotaKey = "store_{$storeId}_fraud_quota_{$today}";
        $usedToday = (int) Cache::get($quotaKey, 0);
        $dailyLimit = (int) Utility::GetValueByName('fraud_daily_limit', $storeId) ?: 20;

        if ($usedToday >= $dailyLimit && !$request->boolean('force')) {
            return response()->json([
                'status' => 'quota_exceeded',
                'used' => $usedToday,
                'limit' => $dailyLimit,
                'message' => 'আজকের দৈনিক ফ্রি চেকের লিমিট শেষ হয়ে গেছে আনলিমিটেড ব্যবহার করতে API সাবস্ক্রিপশন আপগ্রেড করুন'
            ]);
        }

        // Cache or worker call
        $cacheKey = "fraud_cache_{$cleanPhone}";
        $courierData = Cache::get($cacheKey);

        if (!$courierData || $request->boolean('force_refresh')) {
            $rawApiKey = FraudApiKey::acquireKey('BD Courier', $storeId);
            // Prefix with store ID as expected by worker: STORE_<store_id>_<original_key>
            $apiKey = "STORE_{$storeId}_{$rawApiKey}";
            $workerUrl = env('FRAUD_WORKER_URL', 'http://127.0.0.1:3005');

            try {
                $response = Http::timeout(4.5)->get("{$workerUrl}/check", [
                    'phone' => $cleanPhone,
                    'api_key' => $apiKey
                ]);

                if ($response->successful()) {
                    $courierData = $response->json();
                    $totalOrders = (int) ($courierData['total_orders'] ?? 0);
                    $ttl = ($totalOrders >= 5) ? (72 * 3600) : (24 * 3600);
                    Cache::put($cacheKey, $courierData, $ttl);
                }
            } catch (\Throwable $e) {
                return response()->json(['status' => 'error', 'message' => 'কুরিয়ার সার্ভিসে সংযোগ পাওয়া যাচ্ছে না পরে চেষ্টা করুন'], 503);
            }
        }

        // Increment quota
        Cache::put($quotaKey, $usedToday + 1, now()->endOfDay());

        // Log manual check
        FraudCheckLog::create([
            'store_id' => $storeId,
            'phone' => $cleanPhone,
            'courier_details' => is_array($courierData['courier_details'] ?? null) ? $courierData['courier_details'] : ['summary' => (string) ($courierData['courier_details'] ?? '-')],
            'total_orders' => $courierData['total_orders'] ?? 0,
            'total_delivery' => $courierData['total_delivery'] ?? 0,
            'total_cancel' => $courierData['total_cancel'] ?? 0,
            'success_rate' => $courierData['success_rate'] ?? 0,
            'risk_status' => (($courierData['success_rate'] ?? 100) < 50) ? 'high' : 'safe',
            'is_blocked' => false,
            'check_type' => 'manual_admin',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $courierData,
            'used_today' => $usedToday + 1,
            'daily_limit' => $dailyLimit
        ]);
    }
}
