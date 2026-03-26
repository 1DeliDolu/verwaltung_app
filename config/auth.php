<?php

$mfaEmailChallengeRoles = array_values(array_filter(array_map(
    static fn (string $role): string => trim($role),
    explode(',', (string) env('AUTH_MFA_EMAIL_CHALLENGE_ROLES', 'admin'))
)));

return [
    'session_key' => 'auth_user',
    'csrf_key' => '_csrf_token',
    'login_path' => '/login',
    'pending_mfa_key' => 'auth_pending_mfa',
    'login_throttle' => [
        'max_attempts' => (int) env('AUTH_LOGIN_MAX_ATTEMPTS', 5),
        'decay_seconds' => (int) env('AUTH_LOGIN_DECAY_SECONDS', 900),
    ],
    'mfa' => [
        'email_challenge' => [
            'enabled_roles' => $mfaEmailChallengeRoles,
            'expire_seconds' => (int) env('AUTH_MFA_EMAIL_CHALLENGE_EXPIRE_SECONDS', 600),
        ],
    ],
    'password_reset' => [
        'expire_seconds' => (int) env('AUTH_PASSWORD_RESET_EXPIRE_SECONDS', 3600),
        'request_throttle' => [
            'max_attempts' => (int) env('AUTH_PASSWORD_RESET_REQUEST_MAX_ATTEMPTS', 3),
            'decay_seconds' => (int) env('AUTH_PASSWORD_RESET_REQUEST_DECAY_SECONDS', 900),
        ],
    ],
];
