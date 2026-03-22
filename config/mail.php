<?php

return [
    'host' => env('MAIL_HOST', '127.0.0.1'),
    'port' => (int) env('MAIL_PORT', 1025),
    'from_address' => env('MAIL_FROM_ADDRESS', 'probe@verwaltung.demo'),
    'from_name' => env('MAIL_FROM_NAME', 'Verwaltung Probe'),
    'test_recipient' => env('MAIL_TEST_RECIPIENT', 'admin@verwaltung.demo'),
];
