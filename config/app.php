<?php

return [
    'name' => env('APP_NAME', 'Verwaltung App'),
    'env' => env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
    'demo_mode' => filter_var(env('APP_DEMO_MODE', false), FILTER_VALIDATE_BOOL),
];
