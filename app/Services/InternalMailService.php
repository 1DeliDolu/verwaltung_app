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
