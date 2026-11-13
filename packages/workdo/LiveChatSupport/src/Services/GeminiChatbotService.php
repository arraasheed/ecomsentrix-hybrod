<?php

namespace Workdo\LiveChatSupport\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;

class GeminiChatbotService
{
    public function reply(string $message, ?int $storeId = null): string
    {
        $settings = $this->settings($storeId);
        $apiKey = $settings['api_key'];
        if ($apiKey === '') {
            throw new \RuntimeException('Live chat AI API key is not configured.');
        }

        if ($settings['provider'] === 'gemini') {
            return $this->requestGemini($message, $settings['model'], $apiKey);
        }

        return $this->requestOpenAiCompatible(
            $message,
            $settings['model'],
            $apiKey,
            $settings['provider'] === 'custom' ? (string) env('LIVE_CHAT_CUSTOM_GATEWAY_URL', '') : 'https://api.openai.com/v1/chat/completions'
        );
    }

    private function settings(?int $storeId): array
    {
        $values = $storeId
            ? Setting::where('store_id', $storeId)->whereIn('name', [
                'livechat_ai_provider',
                'livechat_ai_model',
                'livechat_api_key',
            ])->pluck('value', 'name')->toArray()
            : [];

        return [
            'provider' => $values['livechat_ai_provider'] ?? 'gemini',
            'model' => $values['livechat_ai_model'] ?? (string) config('chat.gemini_model', ''),
            'api_key' => $values['livechat_api_key'] ?? (string) config('chat.gemini_api_key', ''),
        ];
    }

    private function requestGemini(string $message, string $model, string $apiKey): string
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
            . rawurlencode($model)
            . ':generateContent?key='
            . rawurlencode($apiKey);

        $response = Http::timeout(20)->acceptJson()->post($url, [
            'system_instruction' => [
                'parts' => [[
                    'text' => 'You are a polite, concise sales and customer support assistant '
                        . 'for an e-commerce store. Support Bengali and English, reply in the '
                        . 'language used by the customer, never invent order details, and ask '
                        . 'for an order number when an order lookup is needed.',
                ]],
            ],
            'contents' => [[
                'role' => 'user',
                'parts' => [['text' => $message]],
            ]],
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 512,
            ],
        ]);

        return $this->responseText($response, 'candidates.0.content.parts.0.text', 'Gemini');
    }

    private function requestOpenAiCompatible(string $message, string $model, string $apiKey, string $url): string
    {
        if ($url === '') {
            throw new \RuntimeException('Custom AI gateway URL is not configured.');
        }

        $response = Http::timeout(20)
            ->withToken($apiKey)
            ->acceptJson()
            ->post($url, [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a polite, concise sales and customer support assistant '
                            . 'for an e-commerce store. Support Bengali and English, reply in the '
                            . 'language used by the customer, never invent order details, and ask '
                            . 'for an order number when an order lookup is needed.',
                    ],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.4,
                'max_tokens' => 512,
            ]);

        return $this->responseText($response, 'choices.0.message.content', 'AI');
    }

    private function responseText($response, string $path, string $provider): string
    {
        try {
            $response->throw();
        } catch (RequestException $exception) {
            throw new \RuntimeException($provider . ' API request failed.', 0, $exception);
        }

        $text = $response->json($path);
        if (!is_string($text) || trim($text) === '') {
            throw new \RuntimeException($provider . ' API returned an empty response.');
        }

        return trim($text);
    }
}
