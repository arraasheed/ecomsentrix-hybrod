<?php

namespace Workdo\LiveChatSupport\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Workdo\LiveChatSupport\Models\ChatConversation;

class AdminChatInboxController extends Controller
{
    public function index()
    {
        $storeId = (int) getCurrentStore();
        $conversations = ChatConversation::with('messages')
            ->where('store_id', $storeId)
            ->latest()
            ->paginate(20);

        $settings = Setting::where('store_id', $storeId)
            ->whereIn('name', ['livechat_ai_provider', 'livechat_ai_model', 'livechat_api_key'])
            ->pluck('value', 'name');

        return view('livechatsupport::admin.inbox', [
            'conversations' => $conversations,
            'aiProvider' => $settings->get('livechat_ai_provider', 'gemini'),
            'aiModel' => $settings->get('livechat_ai_model', config('chat.gemini_model', '')),
            'hasApiKey' => filled($settings->get('livechat_api_key', config('chat.gemini_api_key', ''))),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'ai_provider' => ['required', 'in:gemini,openai,custom'],
            'ai_model' => ['required', 'string', 'max:150'],
            'api_key' => ['nullable', 'string', 'max:1000'],
        ]);

        $storeId = (int) getCurrentStore();
        $this->saveSetting('livechat_ai_provider', $data['ai_provider'], $storeId);
        $this->saveSetting('livechat_ai_model', $data['ai_model'], $storeId);

        if (filled($data['api_key'] ?? null)) {
            $this->saveSetting('livechat_api_key', $data['api_key'], $storeId);
        }

        return redirect()->route('livechat.admin.index')
            ->with('success', __('Live chat AI settings saved.'));
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

    private function saveSetting(string $name, string $value, int $storeId): void
    {
        Setting::updateOrCreate(
            ['name' => $name, 'store_id' => $storeId],
            ['value' => $value]
        );
        Cache::forget("settings_store_{$storeId}");
    }
}
