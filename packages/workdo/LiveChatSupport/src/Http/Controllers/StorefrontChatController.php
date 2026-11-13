<?php

namespace Workdo\LiveChatSupport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Workdo\LiveChatSupport\Models\ChatConversation;
use Workdo\LiveChatSupport\Services\GeminiChatbotService;

class StorefrontChatController extends Controller
{
    public function init(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
        ]);

        $conversation = ChatConversation::create([
            'store_id' => $data['store_id'],
            'session_token' => Str::random(64),
            'customer_name' => $data['customer_name'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'status' => 'ai_active',
        ]);

        return response()->json([
            'conversation_id' => $conversation->id,
            'session_token' => $conversation->session_token,
        ]);
    }

    public function send(Request $request, GeminiChatbotService $chatbot): JsonResponse
    {
        $data = $request->validate([
            'conversation_id' => ['required', 'integer'],
            'session_token' => ['required', 'string', 'size:64'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $conversation = ChatConversation::whereKey($data['conversation_id'])
            ->where('session_token', $data['session_token'])
            ->firstOrFail();

        if ($conversation->status === 'closed') {
            return response()->json(['message' => 'This conversation is closed.'], 422);
        }

        $conversation->messages()->create([
            'sender_type' => 'visitor',
            'message' => $data['message'],
        ]);

        try {
            $reply = $chatbot->reply($data['message']);
        } catch (\RuntimeException $exception) {
            Log::error('Live chat Gemini response failed.', [
                'conversation_id' => $conversation->id,
                'exception' => $exception,
            ]);

            return response()->json([
                'message' => 'The support assistant is temporarily unavailable.',
            ], 503);
        }

        $botMessage = $conversation->messages()->create([
            'sender_type' => 'bot',
            'message' => $reply,
        ]);

        return response()->json([
            'message' => $botMessage->only(['id', 'sender_type', 'message', 'created_at']),
        ]);
    }
}
