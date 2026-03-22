<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use RuntimeException;

final class MailService
{
    public function __construct(private readonly App $app)
    {
    }

    public function sendTestMessage(string $to, string $subject, string $body): void
    {
        $this->sendMessage($to, $subject, $body);
    }

    public function sendMessage(string $to, string $subject, string $body, ?string $fromAddress = null, ?string $fromName = null): void
    {
        $host = (string) $this->app->config('mail.host', '127.0.0.1');
        $port = (int) $this->app->config('mail.port', 1025);
        $fromAddress ??= (string) $this->app->config('mail.from_address', 'probe@verwaltung.demo');
        $fromName ??= (string) $this->app->config('mail.from_name', 'Verwaltung Probe');

        $socket = @fsockopen($host, $port, $errno, $errstr, 10);

        if (!is_resource($socket)) {
            throw new RuntimeException(sprintf('SMTP connection failed: %s (%d)', $errstr, $errno));
        }

        stream_set_timeout($socket, 10);

        $this->expect($socket, [220]);
        $this->command($socket, 'EHLO verwaltung.demo', [250]);
        $this->command($socket, 'MAIL FROM:<' . $fromAddress . '>', [250]);
        $this->command($socket, 'RCPT TO:<' . $to . '>', [250]);
        $this->command($socket, 'DATA', [354]);

        $headers = [
            'From: ' . $fromName . ' <' . $fromAddress . '>',
            'To: <' . $to . '>',
            'Subject: ' . $subject,
            'Date: ' . date(DATE_RFC2822),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
        ];

        $message = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
        $this->command($socket, $message, [250]);
        $this->command($socket, 'QUIT', [221]);

        fclose($socket);
    }

    public function mailboxFor(string $email): array
    {
        $apiUrl = (string) $this->app->config('mail.mailhog_api_url', '');

        if ($apiUrl === '') {
            return ['inbox' => [], 'sent' => []];
        }

        $json = @file_get_contents($apiUrl);

        if ($json === false) {
            throw new RuntimeException('MailHog API could not be reached.');
        }

        $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $items = $payload['items'] ?? [];
        $inbox = [];
        $sent = [];

        foreach ($items as $item) {
            $fromEmail = sprintf(
                '%s@%s',
                $item['From']['Mailbox'] ?? '',
                $item['From']['Domain'] ?? ''
            );

            $toList = [];

            foreach ($item['To'] ?? [] as $recipient) {
                $toList[] = sprintf(
                    '%s@%s',
                    $recipient['Mailbox'] ?? '',
                    $recipient['Domain'] ?? ''
                );
            }

            $normalized = [
                'subject' => $item['Content']['Headers']['Subject'][0] ?? '(ohne Betreff)',
                'from' => $fromEmail,
                'to' => $toList,
                'body' => $item['Content']['Body'] ?? '',
                'created_at' => $item['Created'] ?? null,
            ];

            if (in_array($email, $toList, true)) {
                $inbox[] = $normalized;
            }

            if ($fromEmail === $email) {
                $sent[] = $normalized;
            }
        }

        return [
            'inbox' => $inbox,
            'sent' => $sent,
        ];
    }

    private function command($socket, string $command, array $validCodes): void
    {
        fwrite($socket, $command . "\r\n");
        $this->expect($socket, $validCodes);
    }

    private function expect($socket, array $validCodes): void
    {
        $response = '';

        while (($line = fgets($socket)) !== false) {
            $response .= $line;

            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);

        if (!in_array($code, $validCodes, true)) {
            throw new RuntimeException('Unexpected SMTP response: ' . trim($response));
        }
    }
}
