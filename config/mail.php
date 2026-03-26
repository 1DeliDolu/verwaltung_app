<?php

$auditReportRecipients = array_values(array_filter(array_map(
    static fn (string $recipient): string => trim($recipient),
    explode(',', (string) env('MAIL_AUDIT_REPORT_RECIPIENTS', ''))
)));

return [
    'host' => env('MAIL_HOST', '127.0.0.1'),
    'port' => (int) env('MAIL_PORT', 1025),
    'from_address' => env('MAIL_FROM_ADDRESS', 'probe@verwaltung.demo'),
    'from_name' => env('MAIL_FROM_NAME', 'Verwaltung Probe'),
    'test_recipient' => env('MAIL_TEST_RECIPIENT', 'admin@verwaltung.demo'),
    'audit_report_recipients' => $auditReportRecipients,
    'audit_report_now' => env('MAIL_AUDIT_REPORT_NOW', ''),
    'capture_path' => env('MAIL_CAPTURE_PATH', ''),
    'mailhog_api_url' => env('MAILHOG_API_URL', 'http://127.0.0.1:8025/api/v2/messages'),
];
