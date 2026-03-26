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

        $capturePath = trim((string) ($options['capture_path'] ?? $this->app->config('mail.capture_path', '')));

        $host = (string) $this->app->config('mail.host', '127.0.0.1');
        $port = (int) $this->app->config('mail.port', 1025);
        $fromAddress ??= (string) $this->app->config('mail.from_address', 'probe@verwaltung.demo');
        $fromName ??= (string) $this->app->config('mail.from_name', 'Verwaltung Probe');

        if ($capturePath !== '') {
            $this->captureMessage($capturePath, [
                'to' => $recipients,
                'subject' => $subject,
                'text_body' => $textBody,
                'html_body' => isset($options['html_body']) ? (string) $options['html_body'] : null,
                'from_address' => $fromAddress,
                'from_name' => $fromName,
                'template' => isset($options['template']) ? (string) $options['template'] : null,
                'attachments' => array_map(static function (array $attachment): array {
                    return [
                        'name' => (string) ($attachment['name'] ?? ''),
                        'mime' => (string) ($attachment['mime'] ?? 'application/octet-stream'),
                        'content_base64' => base64_encode((string) ($attachment['content'] ?? '')),
                    ];
                }, (array) ($options['attachments'] ?? [])),
                'captured_at' => date(DATE_ATOM),
            ]);

            return;
        }

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
        $items = $this->mailhogItems();
        $inbox = [];
        $sent = [];

        foreach ($items as $item) {
            $fromEmail = sprintf('%s@%s', $item['From']['Mailbox'] ?? '', $item['From']['Domain'] ?? '');
            $toList = [];

            foreach ($item['To'] ?? [] as $recipient) {
                $toList[] = sprintf('%s@%s', $recipient['Mailbox'] ?? '', $recipient['Domain'] ?? '');
            }

            $normalized = [
                'message_id' => (string) ($item['ID'] ?? ''),
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

    public function downloadAttachmentFor(string $email, string $messageId, string $filename): array
    {
        foreach ($this->mailhogItems() as $item) {
            if ((string) ($item['ID'] ?? '') !== $messageId) {
                continue;
            }

            $fromEmail = sprintf('%s@%s', $item['From']['Mailbox'] ?? '', $item['From']['Domain'] ?? '');
            $toList = [];

            foreach ($item['To'] ?? [] as $recipient) {
                $toList[] = sprintf('%s@%s', $recipient['Mailbox'] ?? '', $recipient['Domain'] ?? '');
            }

            if ($fromEmail !== $email && !in_array($email, $toList, true)) {
                throw new RuntimeException('Attachment access denied.');
            }

            $attachment = $this->findAttachmentByName($item['MIME'] ?? null, $filename);

            if ($attachment === null) {
                throw new RuntimeException('Attachment not found.');
            }

            return $attachment;
        }

        throw new RuntimeException('Message not found.');
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
        return $this->extractMimeAttachments($item['MIME'] ?? null, (string) ($item['ID'] ?? ''));
    }

    private function extractMimeAttachments(mixed $mime, string $messageId): array
    {
        if (!is_array($mime)) {
            return [];
        }

        $attachments = [];
        $headers = $mime['Headers'] ?? [];
        $disposition = (string) ($headers['Content-Disposition'][0] ?? '');
        $contentType = (string) ($headers['Content-Type'][0] ?? 'application/octet-stream');
        $filename = $this->extractFilename($disposition, $contentType);

        if ($filename !== null) {
            $attachments[] = [
                'name' => $filename,
                'mime' => trim(strtok($contentType, ';')) ?: 'application/octet-stream',
                'download_url' => '/mail/attachments/' . rawurlencode($messageId) . '/' . rawurlencode($filename),
            ];
        }

        foreach ($mime['Parts'] ?? [] as $part) {
            $attachments = array_merge($attachments, $this->extractMimeAttachments($part, $messageId));
        }

        return $attachments;
    }

    private function findAttachmentByName(mixed $mime, string $filename): ?array
    {
        if (!is_array($mime)) {
            return null;
        }

        $headers = $mime['Headers'] ?? [];
        $contentType = (string) ($headers['Content-Type'][0] ?? 'application/octet-stream');
        $disposition = (string) ($headers['Content-Disposition'][0] ?? '');
        $detectedFilename = $this->extractFilename($disposition, $contentType);

        if ($detectedFilename === $filename) {
            $body = (string) ($mime['Body'] ?? '');
            $encoding = strtolower((string) ($headers['Content-Transfer-Encoding'][0] ?? ''));
            $content = $encoding === 'base64' ? base64_decode(str_replace(["\r", "\n"], '', $body), true) : $body;

            if ($content === false) {
                throw new RuntimeException('Attachment could not be decoded.');
            }

            return [
                'name' => $detectedFilename,
                'mime' => trim(strtok($contentType, ';')) ?: 'application/octet-stream',
                'content' => $content,
            ];
        }

        foreach ($mime['Parts'] ?? [] as $part) {
            $attachment = $this->findAttachmentByName($part, $filename);

            if ($attachment !== null) {
                return $attachment;
            }
        }

        return null;
    }

    private function extractFilename(string $disposition, string $contentType): ?string
    {
        if (preg_match('/filename="([^"]+)"/i', $disposition, $matches)) {
            return $matches[1];
        }

        if (preg_match('/name="([^"]+)"/i', $contentType, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function mailhogItems(): array
    {
        $apiUrl = (string) $this->app->config('mail.mailhog_api_url', '');

        if ($apiUrl === '') {
            return [];
        }

        $json = @file_get_contents($apiUrl);

        if ($json === false) {
            throw new RuntimeException('MailHog API could not be reached.');
        }

        $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return $payload['items'] ?? [];
    }

    private function captureMessage(string $capturePath, array $payload): void
    {
        $directory = dirname($capturePath);

        if ($directory !== '' && !is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException('Mail capture directory could not be created.');
        }

        $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES);

        if ($encoded === false) {
            throw new RuntimeException('Mail capture payload could not be encoded.');
        }

        $written = file_put_contents($capturePath, $encoded . PHP_EOL, FILE_APPEND | LOCK_EX);

        if ($written === false) {
            throw new RuntimeException('Mail capture payload could not be written.');
        }
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
