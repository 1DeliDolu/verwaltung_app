<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\AuditLogService;
use App\Services\InternalMailService;

final class InternalMailController extends Controller
{
    public function audit(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new InternalMailService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'action' => trim((string) $request->input('action', '')),
            'outcome' => trim((string) $request->input('outcome', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $events = array_values(array_filter(
            $audit->readMailActivityEvents($filters),
            static function (array $event) use ($user): bool {
                if ((string) ($user['role_name'] ?? '') === 'admin') {
                    return true;
                }

                $userEmail = (string) ($user['email'] ?? '');

                return $userEmail !== '' && (
                    (string) ($event['actor']['email'] ?? '') === $userEmail
                    || (string) ($event['mail']['sender_email'] ?? '') === $userEmail
                    || in_array($userEmail, (array) ($event['mail']['recipients'] ?? []), true)
                );
            }
        ));

        if ((string) $request->input('format', '') === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="mail-activity-audit.csv"');
            echo $audit->mailActivityEventsAsCsv($events);
            return;
        }

        $this->render('mail/audit', [
            'app' => $this->app,
            'user' => $user,
            'events' => $events,
            'filters' => $filters,
            'actionOptions' => [
                'send_mail' => 'Senden',
                'read_mail' => 'Lesen',
                'archive_mail' => 'Archivieren',
                'restore_mail' => 'Wiederherstellen',
                'download_attachment' => 'Anhang Download',
            ],
            'outcomeOptions' => [
                'success' => 'Erfolg',
                'failure' => 'Fehler',
            ],
        ]);
    }

    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new InternalMailService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();
        $scopes = (array) $request->input('scope', []);

        if ($scopes === []) {
            $scopes = ['all'];
        }

        $filters = [
            'term' => (string) $request->input('search', ''),
            'scope' => array_values(array_unique(array_filter(array_map(
                static fn (mixed $scope): string => trim((string) $scope),
                $scopes
            )))),
        ];
        $mailbox = $service->mailbox($user, $filters);
        $composeMode = trim((string) $request->input('compose', ''));
        $composeTarget = trim((string) $request->input('target', ''));
        $composeFolder = trim((string) $request->input('folder', ''));
        $composePrefill = $service->composePrefill($user, $mailbox, $composeMode, $composeTarget, $composeFolder);
        $oldRecipients = (array) $this->app->session()->consumeFlash('mail_old_recipients', []);
        $oldRecipient = (string) $this->app->session()->consumeFlash('mail_old_recipient', '');
        $oldSubject = (string) $this->app->session()->consumeFlash('mail_old_subject', '');
        $oldBody = (string) $this->app->session()->consumeFlash('mail_old_body', '');

        $this->render('mail/index', [
            'app' => $this->app,
            'user' => $user,
            'directory' => $service->recipientDirectory($user),
            'inbox' => $mailbox['inbox'],
            'sent' => $mailbox['sent'],
            'archived' => $mailbox['archived'],
            'csrfToken' => CsrfMiddleware::token($this->app),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
            'filters' => $filters,
            'composePrefill' => $composePrefill,
            'old' => [
                'recipient_emails' => $oldRecipients !== [] ? $oldRecipients : ($composePrefill['recipient_emails'] ?? []),
                'recipient_email' => $oldRecipient !== '' ? $oldRecipient : (string) ($composePrefill['recipient_email'] ?? ''),
                'subject' => $oldSubject !== '' ? $oldSubject : (string) ($composePrefill['subject'] ?? ''),
                'body' => $oldBody !== '' ? $oldBody : (string) ($composePrefill['body'] ?? ''),
                'compose_mode' => (string) ($composePrefill['mode'] ?? ''),
            ],
        ]);
    }

    public function send(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new InternalMailService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();

        $payload = [
            'recipient_emails' => (array) $request->input('recipient_emails', []),
            'recipient_email' => (string) $request->input('recipient_email', ''),
            'subject' => (string) $request->input('subject', ''),
            'body' => (string) $request->input('body', ''),
            'attachment' => $request->file('attachment'),
        ];

        try {
            $recipientEmails = (array) $payload['recipient_emails'];

            if ($recipientEmails === [] && (string) $payload['recipient_email'] !== '') {
                $recipientEmails = [(string) $payload['recipient_email']];
            }

            $mailId = $service->sendInternalMail($user, $payload);
            $audit->recordMailActivityEvent('send_mail', [
                'actor' => $user,
                'mail' => [
                    'id' => $mailId,
                    'subject' => $payload['subject'],
                    'sender_email' => (string) ($user['email'] ?? ''),
                    'recipients' => $recipientEmails,
                ],
                'metadata' => [
                    'recipient_count' => count($recipientEmails),
                    'attachment_name' => is_array($payload['attachment']) ? (string) ($payload['attachment']['name'] ?? '') : null,
                    'folder' => 'sent',
                ],
            ]);
            $this->app->session()->flash('success', 'Interne Mail wurde gesendet.');
        } catch (\RuntimeException $exception) {
            $recipientEmails = (array) $payload['recipient_emails'];

            if ($recipientEmails === [] && (string) $payload['recipient_email'] !== '') {
                $recipientEmails = [(string) $payload['recipient_email']];
            }

            $audit->recordMailActivityEvent('send_mail', [
                'actor' => $user,
                'mail' => [
                    'subject' => $payload['subject'],
                    'sender_email' => (string) ($user['email'] ?? ''),
                    'recipients' => $recipientEmails,
                ],
                'metadata' => [
                    'recipient_count' => count($recipientEmails),
                    'attachment_name' => is_array($payload['attachment']) ? (string) ($payload['attachment']['name'] ?? '') : null,
                    'folder' => 'compose',
                ],
                'outcome' => 'failure',
                'reason' => $exception->getMessage(),
            ]);
            $this->app->session()->flash('error', 'Interne Mail konnte nicht gesendet werden.');
            $this->app->session()->flash('mail_old_recipients', $payload['recipient_emails']);
            $this->app->session()->flash('mail_old_recipient', $payload['recipient_email']);
            $this->app->session()->flash('mail_old_subject', $payload['subject']);
            $this->app->session()->flash('mail_old_body', $payload['body']);
        }

        $this->redirect('/mail');
    }

    public function downloadAttachment(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new InternalMailService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();
        $mailId = (int) ($params['mailId'] ?? 0);
        $attachmentId = (int) ($params['attachmentId'] ?? 0);
        $attachment = \App\Models\InternalMail::attachmentForUser((int) $user['id'], $mailId, $attachmentId);
        $message = $mailId > 0 ? $service->findMessage($user, $mailId) : null;

        if ($attachment === null) {
            $audit->recordMailActivityEvent('download_attachment', [
                'actor' => $user,
                'mail' => $message ?? ['id' => $mailId],
                'metadata' => [
                    'attachment_name' => '',
                    'folder' => 'unknown',
                ],
                'outcome' => 'failure',
                'reason' => 'Attachment not found.',
            ]);
            http_response_code(404);
            echo 'Attachment not found.';
            return;
        }

        $audit->recordMailActivityEvent('download_attachment', [
            'actor' => $user,
            'mail' => $message ?? ['id' => $mailId],
            'metadata' => [
                'attachment_name' => (string) ($attachment['original_name'] ?? ''),
                'folder' => ($message !== null && !empty($message['is_archived'])) ? 'archived' : 'mailbox',
            ],
        ]);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . ($attachment['mime_type'] ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . addslashes((string) $attachment['original_name']) . '"');
        header('Content-Length: ' . strlen((string) $attachment['file_content']));
        echo $attachment['file_content'];
        exit;
    }

    public function markRead(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new InternalMailService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();
        $mailId = (int) ($params['mailId'] ?? 0);
        $marked = false;
        $message = $mailId > 0 ? $service->findMessage($user, $mailId) : null;

        if ($mailId > 0) {
            $marked = $service->markMessageAsRead($user, $mailId);
        }

        $audit->recordMailActivityEvent('read_mail', [
            'actor' => $user,
            'mail' => $message ?? ['id' => $mailId],
            'metadata' => [
                'folder' => 'inbox',
            ],
            'outcome' => $marked ? 'success' : 'failure',
            'reason' => $marked ? null : 'Mail could not be marked as read.',
        ]);

        header('Content-Type: application/json');
        echo json_encode([
            'ok' => true,
            'marked' => $marked,
            'unread_count' => $service->inboxCount($user),
        ]);
        exit;
    }

    public function archive(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new InternalMailService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();
        $mailId = (int) ($params['mailId'] ?? 0);
        $archived = false;
        $message = $mailId > 0 ? $service->findMessage($user, $mailId) : null;

        if ($mailId > 0) {
            $archived = $service->archiveMessage($user, $mailId);
        }

        $audit->recordMailActivityEvent('archive_mail', [
            'actor' => $user,
            'mail' => $message ?? ['id' => $mailId],
            'metadata' => [
                'folder' => $message !== null && (string) ($message['from'] ?? '') === (string) ($user['email'] ?? '') ? 'sent' : 'inbox',
            ],
            'outcome' => $archived ? 'success' : 'failure',
            'reason' => $archived ? null : 'Mail could not be archived.',
        ]);

        header('Content-Type: application/json');
        echo json_encode([
            'ok' => true,
            'archived' => $archived,
            'unread_count' => $service->inboxCount($user),
        ]);
        exit;
    }

    public function restore(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new InternalMailService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();
        $mailId = (int) ($params['mailId'] ?? 0);
        $restored = false;
        $message = $mailId > 0 ? $service->findMessage($user, $mailId) : null;

        if ($mailId > 0) {
            $restored = $service->restoreMessage($user, $mailId);
        }

        $audit->recordMailActivityEvent('restore_mail', [
            'actor' => $user,
            'mail' => $message ?? ['id' => $mailId],
            'metadata' => [
                'folder' => 'archived',
            ],
            'outcome' => $restored ? 'success' : 'failure',
            'reason' => $restored ? null : 'Mail could not be restored.',
        ]);

        header('Content-Type: application/json');
        echo json_encode([
            'ok' => true,
            'restored' => $restored,
            'unread_count' => $service->inboxCount($user),
        ]);
        exit;
    }
}
