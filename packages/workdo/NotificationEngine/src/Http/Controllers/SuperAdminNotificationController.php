<?php

namespace Workdo\NotificationEngine\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\NotificationEngine\Services\NotificationService;

class SuperAdminNotificationController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()?->type === 'super admin', 403);

        return view('notificationengine::admin.index', [
            'stores' => Store::whereNotNull('created_by')->orderBy('name')->get(['id', 'name', 'created_by']),
        ]);
    }

    public function store(Request $request, NotificationService $notifications)
    {
        abort_unless(Auth::user()?->type === 'super admin', 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'type' => ['required', 'in:order,billing,announcement,alert'],
            'recipient_type' => ['required', 'in:all_stores,store_admin'],
            'store_id' => ['required_if:recipient_type,store_admin', 'nullable', 'integer', 'exists:stores,id'],
            'target_url' => ['nullable', 'url', 'max:2048'],
        ]);

        if ($data['recipient_type'] === 'all_stores') {
            $notifications->broadcast(
                (int) Auth::id(),
                $data['title'],
                $data['message'],
                $data['type'],
                $data['target_url'] ?? null
            );
        } else {
            $store = Store::findOrFail($data['store_id']);
            $notifications->notifyStoreAdmin(
                (int) Auth::id(),
                (int) $store->created_by,
                $data['title'],
                $data['message'],
                $data['type'],
                $data['target_url'] ?? null
            );
        }

        return redirect()->route('notificationengine.admin.index')
            ->with('success', __('Notification dispatched successfully.'));
    }
}
