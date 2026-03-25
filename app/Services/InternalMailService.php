<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Models\InternalMail;
use App\Models\User;
use RuntimeException;

final class InternalMailService
{
    public function __construct(private readonly App $app)
    {
    }

    public function currentUser(): array
    {
        $authUser = $this->app->session()->get((string) $this->app->config('auth.session_key', 'auth_user'));
        $userId = (int) ($authUser['id'] ?? 0);
        $user = User::findById($userId);

        if ($user === null) {
            throw new RuntimeException('Authenticated user could not be loaded.');
        }

        return $user;
    }

    public function recipientDirectory(?array $excludeUser = null): array
    {
        $directory = User::internalDirectory();

        if ($excludeUser === null) {
            return $directory;
        }

        $excludedEmail = (string) ($excludeUser['email'] ?? '');

        return array_values(array_filter(
            $directory,
            static fn (array $entry): bool => (string) ($entry['email'] ?? '') !== $excludedEmail
        ));
    }

    public function sendInternalMail(array $sender, array $input): void
    {
        $recipients = $this->normalizeRecipients($input);
        $subject = trim((string) ($input['subject'] ?? ''));
        $body = trim((string) ($input['body'] ?? ''));
        $attachment = $input['attachment'] ?? null;

        if ($recipients === [] || $subject === '' || $body === '') {
            throw new RuntimeException('All mail fields are required.');
        }

        $recipientRecords = User::findByEmails($recipients);

        if (count($recipientRecords) !== count($recipients)) {
            throw new RuntimeException('One or more recipients are invalid.');
        }

        $templateData = [
            'sender_name' => (string) $sender['name'],
            'sender_email' => (string) $sender['email'],
            'recipient_count' => count($recipientRecords),
            'subject' => $subject,
            'body' => $body,
        ];
        $rendered = (new MailService($this->app))->renderInternalTemplate($templateData);
        $attachments = $this->normalizeAttachments($attachment);

        (new MailService($this->app))->sendMessage(
            $recipients,
            $subject,
            $rendered['text'],
            (string) $sender['email'],
            (string) $sender['name'],
            [
                'template' => 'internal-message',
                'html_body' => $rendered['html'],
                'attachments' => $attachments,
            ]
        );

        InternalMail::create([
            'sender_id' => (int) $sender['id'],
            'sender_name' => (string) $sender['name'],
            'sender_email' => (string) $sender['email'],
            'subject' => $subject,
            'body' => $body,
        ], $recipientRecords, $attachments);
    }

    public function mailbox(array $user, array $filters = []): array
    {
        return InternalMail::mailboxForUser((int) $user['id'], $filters);
    }

    public function inboxCount(array $user): int
    {
        return InternalMail::inboxCountForUser((int) $user['id']);
    }

    public function markMessageAsRead(array $user, int $mailId): bool
    {
        return InternalMail::markAsReadForRecipient((int) $user['id'], $mailId);
    }

    public function archiveMessage(array $user, int $mailId): bool
    {
        return InternalMail::archiveForUser((int) $user['id'], $mailId);
    }

    public function restoreMessage(array $user, int $mailId): bool
    {
        return InternalMail::restoreForUser((int) $user['id'], $mailId);
    }

    public function composePrefill(array $user, array $mailbox, string $mode, string $target, string $folder): array
    {
        if (!in_array($mode, ['reply', 'forward'], true) || $target === '') {
            return [
                'mode' => '',
                'recipient_emails' => [],
                'recipient_email' => '',
                'subject' => '',
                'body' => '',
            ];
        }

        $messages = match ($folder) {
            'sent' => $mailbox['sent'] ?? [],
            'marked' => array_values(array_filter(
                array_merge($mailbox['inbox'] ?? [], $mailbox['sent'] ?? []),
                static fn (array $message): bool => !empty($message['attachments'])
            )),
            default => $mailbox['inbox'] ?? [],
        };

        $selectedMessage = null;

        foreach ($messages as $message) {
            if ((string) ($message['message_id'] ?? '') === $target) {
                $selectedMessage = $message;
                break;
            }
        }

        if ($selectedMessage === null) {
            return [
                'mode' => '',
                'recipient_emails' => [],
                'recipient_email' => '',
                'subject' => '',
                'body' => '',
            ];
        }

        $subjectPrefix = $mode === 'reply' ? 'Re: ' : 'Fwd: ';
        $subject = trim((string) ($selectedMessage['subject'] ?? ''));

        if ($subject !== '' && !str_starts_with(strtolower($subject), strtolower($subjectPrefix))) {
            $subject = $subjectPrefix . $subject;
        }

        $recipientEmails = $mode === 'reply'
            ? array_values(array_filter(
                [(string) ($selectedMessage['from'] ?? '')],
                static fn (string $email): bool => $email !== '' && $email !== (string) ($user['email'] ?? '')
            ))
            : [];

        if ($mode === 'reply') {
            $quotedLines = array_map(
                static fn (string $line): string => $line === '' ? '>' : '> ' . $line,
                preg_split('/\R/', trim((string) ($selectedMessage['body'] ?? ''))) ?: []
            );

            $body = trim(implode(PHP_EOL, [
                '',
                '',
                'Am ' . (string) ($selectedMessage['created_at'] ?? '-') . ' schrieb ' . (string) ($selectedMessage['from'] ?? '-') . ':',
                implode(PHP_EOL, $quotedLines),
            ]));
        } else {
            $body = trim(implode(PHP_EOL, [
                '',
                '--- Weitergeleitete Nachricht ---',
                'Von: ' . (string) ($selectedMessage['from'] ?? '-'),
                'An: ' . implode(', ', $selectedMessage['to'] ?? []),
                'Zeit: ' . (string) ($selectedMessage['created_at'] ?? '-'),
                'Betreff: ' . (string) ($selectedMessage['subject'] ?? '-'),
                '',
                trim((string) ($selectedMessage['body'] ?? '')),
            ]));
        }

        return [
            'mode' => $mode,
            'recipient_emails' => $recipientEmails,
            'recipient_email' => $recipientEmails[0] ?? '',
            'subject' => $subject,
            'body' => $body,
        ];
    }

    private function normalizeAttachments(mixed $attachment): array
    {
        if (!is_array($attachment) || !isset($attachment['error'])) {
            return [];
        }

        if (is_array($attachment['error'])) {
            $attachments = [];

            foreach ($attachment['error'] as $index => $error) {
                if ((int) $error === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                if ((int) $error !== UPLOAD_ERR_OK) {
                    throw new RuntimeException('Attachment upload failed.');
                }

                $tmpName = (string) ($attachment['tmp_name'][$index] ?? '');
                $name = trim((string) ($attachment['name'][$index] ?? ''));

                if ($tmpName === '' || $name === '' || !is_uploaded_file($tmpName)) {
                    throw new RuntimeException('Attachment upload is invalid.');
                }

                $content = file_get_contents($tmpName);

                if ($content === false) {
                    throw new RuntimeException('Attachment could not be read.');
                }

                $attachments[] = [
                    'name' => basename($name),
                    'mime' => (string) ($attachment['type'][$index] ?? 'application/octet-stream'),
                    'content' => $content,
                ];
            }

            return $attachments;
        }

        if (($attachment['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [];
        }

        if (($attachment['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Attachment upload failed.');
        }

        $tmpName = (string) ($attachment['tmp_name'] ?? '');
        $name = trim((string) ($attachment['name'] ?? ''));

        if ($tmpName === '' || $name === '' || !is_uploaded_file($tmpName)) {
            throw new RuntimeException('Attachment upload is invalid.');
        }

        $content = file_get_contents($tmpName);

        if ($content === false) {
            throw new RuntimeException('Attachment could not be read.');
        }

        return [[
            'name' => basename($name),
            'mime' => (string) ($attachment['type'] ?? 'application/octet-stream'),
            'content' => $content,
        ]];
    }

    private function normalizeRecipients(array $input): array
    {
        $rawRecipients = $input['recipient_emails'] ?? [];

        if ($rawRecipients === [] && isset($input['recipient_email'])) {
            $rawRecipients = [$input['recipient_email']];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $recipient): string => trim((string) $recipient),
            (array) $rawRecipients
        )));
    }
}
