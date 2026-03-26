#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Services\AuditWeeklyReportCommandService;

$app = require dirname(__DIR__) . '/bootstrap/console.php';
$options = getopt('', [
    'admin-email::',
    'recipient::',
    'now::',
    'capture-path::',
    'dry-run',
    'help',
]);

if ($options === false) {
    fwrite(STDERR, "Optionen konnten nicht gelesen werden.\n");
    exit(1);
}

if (isset($options['help'])) {
    echo <<<TEXT
Usage:
  php bin/send-weekly-audit-report.php [options]

Options:
  --admin-email=EMAIL     Admin context used for the report.
  --recipient=EMAIL       Override recipient. Can be repeated or comma-separated.
  --now=DATETIME          Override report timestamp/window.
  --capture-path=PATH     Capture payload locally instead of SMTP delivery.
  --dry-run               Print resolved execution data without sending.
  --help                  Show this help.

TEXT;
    exit(0);
}

$nowOption = trim((string) ($options['now'] ?? ''));
$now = null;

if ($nowOption !== '') {
    try {
        $now = new DateTimeImmutable($nowOption);
    } catch (Throwable) {
        fwrite(STDERR, "Ungueltiger --now Wert: {$nowOption}\n");
        exit(1);
    }
}

$recipients = $options['recipient'] ?? [];
$adminEmail = isset($options['admin-email']) ? (string) $options['admin-email'] : null;
$capturePath = isset($options['capture-path']) ? (string) $options['capture-path'] : '';
$command = new AuditWeeklyReportCommandService($app);

try {
    if (isset($options['dry-run'])) {
        $result = $command->preview($adminEmail, $now, [
            'recipients' => $recipients,
        ]);

        echo "Dry run successful.\n";
        echo 'Admin: ' . (string) ($result['admin_email'] ?? '') . "\n";
        echo 'Window: ' . (string) (($result['window']['label'] ?? '')) . "\n";
        echo 'Recipients: ' . implode(', ', (array) ($result['recipients'] ?? [])) . "\n";
        echo 'Subject: ' . (string) ($result['subject'] ?? '') . "\n";
        exit(0);
    }

    $result = $command->send($adminEmail, $now, [
        'recipients' => $recipients,
        'capture_path' => $capturePath,
    ]);

    echo "Weekly audit report sent successfully.\n";
    echo 'Admin: ' . (string) ($result['admin_email'] ?? '') . "\n";
    echo 'Window: ' . (string) (($result['window']['label'] ?? '')) . "\n";
    echo 'Recipients: ' . implode(', ', (array) ($result['recipients'] ?? [])) . "\n";
    echo 'Attachment: ' . (string) ($result['csv_name'] ?? '') . "\n";
    exit(0);
} catch (Throwable $throwable) {
    fwrite(STDERR, 'Weekly audit report failed: ' . $throwable->getMessage() . "\n");
    exit(1);
}
