<?php

namespace App\Http\Controllers;

use App\Models\CentralFraudCheck;
use App\Models\CentralPixelBlock;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CentralIntelligenceController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_fraud_checks' => CentralFraudCheck::count(),
            'blocked_today' => CentralFraudCheck::whereDate('created_at', today())->where('is_blocked', true)->count(),
            'total_pixel_blocks' => CentralPixelBlock::count(),
            'stores_monitored' => Store::count(),
        ];

        $recentFraud = CentralFraudCheck::with('store')->latest()->limit(10)->get();
        $recentPixel = CentralPixelBlock::with('store')->latest()->limit(10)->get();

        return view('superadmin.central-intelligence.dashboard', compact('stats', 'recentFraud', 'recentPixel'));
    }

    public function fraudChecks(Request $request)
    {
        $query = CentralFraudCheck::with('store');
        
        if ($request->filled('phone')) {
            $query->where('phone_number', 'like', '%' . $request->phone . '%');
        }
        
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $fraudChecks = $query->latest()->paginate(50);
        $stores = Store::all();

        return view('superadmin.central-intelligence.fraud-checks', compact('fraudChecks', 'stores'));
    }

    public function searchFraud(Request $request)
    {
        $results = CentralFraudCheck::with('store')
            ->where('phone_number', 'like', '%' . $request->search . '%')
            ->latest()
            ->limit(100)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    public function pixelBlocks(Request $request)
    {
        $query = CentralPixelBlock::with('store');
        
        if ($request->filled('order_id')) {
            $query->where('order_id', $request->order_id);
        }
        
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        $pixelBlocks = $query->latest()->paginate(50);
        $stores = Store::all();

        return view('superadmin.central-intelligence.pixel-blocks', compact('pixelBlocks', 'stores'));
    }

    public function searchPixel(Request $request)
    {
        $results = CentralPixelBlock::with('store')
            ->where('order_id', 'like', '%' . $request->search . '%')
            ->latest()
            ->limit(100)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }
}