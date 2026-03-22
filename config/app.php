<?php

return [
    'name' => env('APP_NAME', 'Verwaltung App'),
    'env' => env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
];
