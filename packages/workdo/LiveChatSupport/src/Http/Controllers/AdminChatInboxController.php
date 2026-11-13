<?php

namespace Workdo\LiveChatSupport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Workdo\LiveChatSupport\Models\ChatConversation;

class AdminChatInboxController extends Controller
{
    public function index()
    {
        $conversations = ChatConversation::with('messages')
            ->where('store_id', getCurrentStore())
            ->latest()
            ->paginate(20);

        return view('livechatsupport::admin.inbox', compact('conversations'));
    }

    public function reply(Request $request, ChatConversation $conversation)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        abort_unless((int) $conversation->store_id === (int) getCurrentStore(), 404);

        $message = $conversation->messages()->create([
            'sender_type' => 'agent',
            'message' => $data['message'],
            'is_read' => true,
        ]);

        $conversation->update(['status' => 'open']);

        return response()->json([
            'message' => $message->only(['id', 'sender_type', 'message', 'created_at']),
        ]);
    }
}
