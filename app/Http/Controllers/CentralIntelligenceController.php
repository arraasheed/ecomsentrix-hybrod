<?php

namespace App\Http\Controllers;

use App\Models\CentralFraudCheck;
use App\Models\CentralPixelBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CentralIntelligenceController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        $results = [];

        if ($query) {
            $results = CentralFraudCheck::where('phone', 'like', "%{$query}%")->paginate(20);
        }

        $totalBlocked = CentralPixelBlock::count();
        $totalFraudChecks = CentralFraudCheck::count();

        return view('admin.central-intelligence.index', compact('results', 'query', 'totalBlocked', 'totalFraudChecks'));
    }
}
