<?php

return [
    'session_key' => 'auth_user',
    'csrf_key' => '_csrf_token',
    'login_path' => '/login',
    'login_throttle' => [
        'max_attempts' => (int) env('AUTH_LOGIN_MAX_ATTEMPTS', 5),
        'decay_seconds' => (int) env('AUTH_LOGIN_DECAY_SECONDS', 900),
    ],
];
