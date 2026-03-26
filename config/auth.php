<?php

return [
    'session_key' => 'auth_user',
    'csrf_key' => '_csrf_token',
    'login_path' => '/login',
    'login_throttle' => [
        'max_attempts' => (int) env('AUTH_LOGIN_MAX_ATTEMPTS', 5),
        'decay_seconds' => (int) env('AUTH_LOGIN_DECAY_SECONDS', 900),
    ],
    'password_reset' => [
        'expire_seconds' => (int) env('AUTH_PASSWORD_RESET_EXPIRE_SECONDS', 3600),
        'request_throttle' => [
            'max_attempts' => (int) env('AUTH_PASSWORD_RESET_REQUEST_MAX_ATTEMPTS', 3),
            'decay_seconds' => (int) env('AUTH_PASSWORD_RESET_REQUEST_DECAY_SECONDS', 900),
        ],
    ],
];
