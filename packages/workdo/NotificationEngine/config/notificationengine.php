<?php

return [
    'enabled' => env('NOTIFICATION_ENGINE_ENABLED', true),
    'poll_interval' => (int) env('NOTIFICATION_ENGINE_POLL_INTERVAL', 15000),
];
