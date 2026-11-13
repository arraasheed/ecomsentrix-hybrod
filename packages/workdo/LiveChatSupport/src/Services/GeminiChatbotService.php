<?php

namespace Workdo\LiveChatSupport\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class GeminiChatbotService
{
    public function reply(string $message): string
    {
        $apiKey = (string) config('chat.gemini_api_key', '');
        if ($apiKey === '') {
            throw new \RuntimeException('Gemini API key is not configured.');
        }

        $model = (string) config('chat.gemini_model', 'gemini-1.5-flash');
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

        try {
            $response->throw();
        } catch (RequestException $exception) {
            throw new \RuntimeException('Gemini API request failed.', 0, $exception);
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        if (!is_string($text) || trim($text) === '') {
            throw new \RuntimeException('Gemini API returned an empty response.');
        }

        return trim($text);
    }
}
