<?php

namespace Workdo\NotificationEngine\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Workdo\NotificationEngine\Models\SystemNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $this->visibleQuery($request);
        $notifications = (clone $query)->latest()->limit(30)->get();

        return response()->json([
            'unread_count' => (clone $query)->where('is_read', false)->count(),
            'notifications' => $notifications,
        ]);
    }

    public function read(Request $request, SystemNotification $notification): JsonResponse
    {
        abort_unless($this->visibleQuery($request)->whereKey($notification->id)->exists(), 404);
        $notification->update(['is_read' => true]);

        return response()->json([
            'target_url' => $notification->target_url,
        ]);
    }

    public function destroy(Request $request, SystemNotification $notification): JsonResponse
    {
        abort_unless($this->visibleQuery($request)->whereKey($notification->id)->exists(), 404);
        $notification->delete();

        return response()->json(['status' => 'deleted']);
    }

    public function clear(Request $request): JsonResponse
    {
        $this->visibleQuery($request)->delete();

        return response()->json(['status' => 'cleared']);
    }

    private function visibleQuery(Request $request)
    {
        if ($request->user('customers')) {
            return SystemNotification::forCustomer((int) $request->user('customers')->id);
        }

        if ($request->user() && $request->user()->type === 'super admin') {
            return SystemNotification::forSuperAdmin();
        }

        abort_unless($request->user(), 401);

        return SystemNotification::forAdmin((int) $request->user()->id);
    }
}
