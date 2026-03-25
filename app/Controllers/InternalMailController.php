<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\InternalMailService;

final class InternalMailController extends Controller
{
    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new InternalMailService($this->app);
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
        $user = $service->currentUser();

        $payload = [
            'recipient_emails' => (array) $request->input('recipient_emails', []),
            'recipient_email' => (string) $request->input('recipient_email', ''),
            'subject' => (string) $request->input('subject', ''),
            'body' => (string) $request->input('body', ''),
            'attachment' => $request->file('attachment'),
        ];

        try {
            $service->sendInternalMail($user, $payload);
            $this->app->session()->flash('success', 'Interne Mail wurde gesendet.');
        } catch (\RuntimeException $exception) {
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
        $user = $service->currentUser();
        $mailId = (int) ($params['mailId'] ?? 0);
        $attachmentId = (int) ($params['attachmentId'] ?? 0);
        $attachment = \App\Models\InternalMail::attachmentForUser((int) $user['id'], $mailId, $attachmentId);

        if ($attachment === null) {
            http_response_code(404);
            echo 'Attachment not found.';
            return;
        }

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
        $user = $service->currentUser();
        $mailId = (int) ($params['mailId'] ?? 0);
        $marked = false;

        if ($mailId > 0) {
            $marked = $service->markMessageAsRead($user, $mailId);
        }

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
        $user = $service->currentUser();
        $mailId = (int) ($params['mailId'] ?? 0);
        $archived = false;

        if ($mailId > 0) {
            $archived = $service->archiveMessage($user, $mailId);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'ok' => true,
            'archived' => $archived,
            'unread_count' => $service->inboxCount($user),
        ]);
        exit;
    }
}
