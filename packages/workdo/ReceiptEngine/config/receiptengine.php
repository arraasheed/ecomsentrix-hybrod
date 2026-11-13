<?php

return [
    'enabled' => env('RECEIPT_ENGINE_ENABLED', true),
    'default_template' => env('RECEIPT_DEFAULT_TEMPLATE', 'corporate'),
    'templates' => ['minimalist', 'corporate', 'thermal', 'premium'],
];
