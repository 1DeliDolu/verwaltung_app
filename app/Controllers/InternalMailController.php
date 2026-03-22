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
        $mailbox = $service->mailbox($user);

        $this->render('mail/index', [
            'app' => $this->app,
            'user' => $user,
            'directory' => $service->recipientDirectory($user),
            'inbox' => $mailbox['inbox'],
            'sent' => $mailbox['sent'],
            'csrfToken' => CsrfMiddleware::token($this->app),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
            'oldRecipientEmail' => (string) $this->app->session()->consumeFlash('mail_old_recipient', ''),
            'old' => [
                'recipient_emails' => (array) $this->app->session()->consumeFlash('mail_old_recipients', []),
                'recipient_email' => (string) $this->app->session()->consumeFlash('mail_old_recipient', ''),
                'subject' => (string) $this->app->session()->consumeFlash('mail_old_subject', ''),
                'body' => (string) $this->app->session()->consumeFlash('mail_old_body', ''),
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
        $messageId = rawurldecode((string) ($params['messageId'] ?? ''));
        $filename = rawurldecode((string) ($params['filename'] ?? ''));

        try {
            $attachment = (new \App\Services\MailService($this->app))->downloadAttachmentFor((string) $user['email'], $messageId, $filename);
        } catch (\RuntimeException $exception) {
            http_response_code(404);
            echo 'Attachment not found.';
            return;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . ($attachment['mime'] ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . addslashes((string) $attachment['name']) . '"');
        header('Content-Length: ' . strlen((string) $attachment['content']));
        echo $attachment['content'];
        exit;
    }
}
