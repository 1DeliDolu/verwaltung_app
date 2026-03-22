<?php

return [
    'host' => env('MAIL_HOST', '127.0.0.1'),
    'port' => (int) env('MAIL_PORT', 1025),
    'from_address' => env('MAIL_FROM_ADDRESS', 'probe@verwaltung.demo'),
    'from_name' => env('MAIL_FROM_NAME', 'Verwaltung Probe'),
    'test_recipient' => env('MAIL_TEST_RECIPIENT', 'admin@verwaltung.demo'),
    'mailhog_api_url' => env('MAILHOG_API_URL', 'http://127.0.0.1:8025/api/v2/messages'),
];
