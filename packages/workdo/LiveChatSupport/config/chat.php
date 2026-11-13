<?php

return [
    'enabled' => env('LIVE_CHAT_ENABLED', true),
    'gemini_api_key' => env('GEMINI_API_KEY', ''),
    'gemini_model' => env('GEMINI_CHAT_MODEL', 'gemini-1.5-flash'),
];
