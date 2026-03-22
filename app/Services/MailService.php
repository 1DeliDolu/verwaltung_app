<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\View;
use RuntimeException;

final class MailService
{
    public function __construct(private readonly App $app)
    {
    }

    public function sendTestMessage(string $to, string $subject, string $body): void
    {
        $this->sendMessage([$to], $subject, $body);
    }

    public function sendMessage(
        array|string $to,
        string $subject,
        string $textBody,
        ?string $fromAddress = null,
        ?string $fromName = null,
        array $options = []
    ): void {
        $recipients = array_values(array_filter(array_map('trim', (array) $to)));

        if ($recipients === []) {
            throw new RuntimeException('At least one recipient is required.');
        }

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

        foreach ($recipients as $recipient) {
            $this->command($socket, 'RCPT TO:<' . $recipient . '>', [250]);
        }

        $this->command($socket, 'DATA', [354]);

        $headers = [
            'From: ' . $fromName . ' <' . $fromAddress . '>',
            'To: ' . implode(', ', array_map(static fn (string $recipient): string => '<' . $recipient . '>', $recipients)),
            'Subject: ' . $subject,
            'Date: ' . date(DATE_RFC2822),
            'MIME-Version: 1.0',
        ];

        if (!empty($options['template'])) {
            $headers[] = 'X-App-Template: ' . (string) $options['template'];
        }

        $body = $this->buildBody(
            $textBody,
            $options['html_body'] ?? null,
            $options['attachments'] ?? [],
            $headers
        );

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
            $fromEmail = sprintf('%s@%s', $item['From']['Mailbox'] ?? '', $item['From']['Domain'] ?? '');
            $toList = [];

            foreach ($item['To'] ?? [] as $recipient) {
                $toList[] = sprintf('%s@%s', $recipient['Mailbox'] ?? '', $recipient['Domain'] ?? '');
            }

            $normalized = [
                'subject' => $item['Content']['Headers']['Subject'][0] ?? '(ohne Betreff)',
                'from' => $fromEmail,
                'to' => $toList,
                'body' => $this->extractBody($item),
                'html_body' => $this->extractHtmlBody($item),
                'created_at' => $item['Created'] ?? null,
                'attachments' => $this->extractAttachments($item),
            ];

            if (in_array($email, $toList, true)) {
                $inbox[] = $normalized;
            }

            if ($fromEmail === $email) {
                $sent[] = $normalized;
            }
        }

        usort(
            $inbox,
            static fn (array $left, array $right): int => strcmp((string) ($right['created_at'] ?? ''), (string) ($left['created_at'] ?? ''))
        );
        usort(
            $sent,
            static fn (array $left, array $right): int => strcmp((string) ($right['created_at'] ?? ''), (string) ($left['created_at'] ?? ''))
        );

        return ['inbox' => $inbox, 'sent' => $sent];
    }

    public function renderInternalTemplate(array $data): array
    {
        return [
            'text' => View::render('mail/templates/internal-message-text', $data, 'plain'),
            'html' => View::render('mail/templates/internal-message-html', $data, 'plain'),
        ];
    }

    private function buildBody(string $textBody, ?string $htmlBody, array $attachments, array &$headers): string
    {
        if ($htmlBody === null && $attachments === []) {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';

            return $textBody;
        }

        $alternativeBoundary = 'alt_' . bin2hex(random_bytes(8));

        if ($attachments === []) {
            $headers[] = 'Content-Type: multipart/alternative; boundary="' . $alternativeBoundary . '"';

            return $this->buildAlternativePart($alternativeBoundary, $textBody, $htmlBody ?? nl2br(htmlspecialchars($textBody, ENT_QUOTES, 'UTF-8')));
        }

        $mixedBoundary = 'mixed_' . bin2hex(random_bytes(8));
        $headers[] = 'Content-Type: multipart/mixed; boundary="' . $mixedBoundary . '"';

        $parts = [];
        $parts[] = '--' . $mixedBoundary;
        $parts[] = 'Content-Type: multipart/alternative; boundary="' . $alternativeBoundary . '"' . "\r\n";
        $parts[] = $this->buildAlternativePart($alternativeBoundary, $textBody, $htmlBody ?? nl2br(htmlspecialchars($textBody, ENT_QUOTES, 'UTF-8')));

        foreach ($attachments as $attachment) {
            $parts[] = '--' . $mixedBoundary;
            $parts[] = 'Content-Type: ' . ($attachment['mime'] ?? 'application/octet-stream') . '; name="' . $attachment['name'] . '"';
            $parts[] = 'Content-Disposition: attachment; filename="' . $attachment['name'] . '"';
            $parts[] = 'Content-Transfer-Encoding: base64';
            $parts[] = '';
            $parts[] = chunk_split(base64_encode((string) $attachment['content']));
        }

        $parts[] = '--' . $mixedBoundary . '--';

        return implode("\r\n", $parts);
    }

    private function buildAlternativePart(string $boundary, string $textBody, string $htmlBody): string
    {
        return implode("\r\n", [
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            '',
            $textBody,
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            '',
            $htmlBody,
            '--' . $boundary . '--',
        ]);
    }

    private function extractBody(array $item): string
    {
        $body = $this->findMimeBody($item['MIME'] ?? null, 'text/plain');

        return $body ?? (string) ($item['Content']['Body'] ?? '');
    }

    private function extractHtmlBody(array $item): ?string
    {
        return $this->findMimeBody($item['MIME'] ?? null, 'text/html');
    }

    private function findMimeBody(mixed $mime, string $targetType): ?string
    {
        if (!is_array($mime)) {
            return null;
        }

        $headers = $mime['Headers'] ?? [];
        $contentType = strtolower((string) ($headers['Content-Type'][0] ?? ''));

        if ($contentType !== '' && str_starts_with($contentType, strtolower($targetType))) {
            return (string) ($mime['Body'] ?? '');
        }

        foreach ($mime['Parts'] ?? [] as $part) {
            $found = $this->findMimeBody($part, $targetType);

            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    private function extractAttachments(array $item): array
    {
        $raw = (string) ($item['Raw']['Data'] ?? '');

        if (!preg_match_all('/filename="([^"]+)"/i', $raw, $matches)) {
            return [];
        }

        return array_values(array_unique($matches[1]));
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
