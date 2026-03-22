<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\VerifiedMiddleware;
use App\Services\InternalMailService;

final class InternalMailController extends Controller
{
    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        VerifiedMiddleware::handle($this->app);

        $service = new InternalMailService($this->app);
        $user = $service->currentUser();
        $mailbox = $service->mailbox($user);

        $this->render('mail/index', [
            'app' => $this->app,
            'user' => $user,
            'directory' => $service->recipientDirectory(),
            'inbox' => $mailbox['inbox'],
            'sent' => $mailbox['sent'],
            'csrfToken' => CsrfMiddleware::token($this->app),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
            'old' => [
                'recipient_email' => (string) $this->app->session()->consumeFlash('mail_old_recipient', ''),
                'subject' => (string) $this->app->session()->consumeFlash('mail_old_subject', ''),
                'body' => (string) $this->app->session()->consumeFlash('mail_old_body', ''),
            ],
        ]);
    }

    public function send(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        VerifiedMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new InternalMailService($this->app);
        $user = $service->currentUser();

        $payload = [
            'recipient_email' => (string) $request->input('recipient_email', ''),
            'subject' => (string) $request->input('subject', ''),
            'body' => (string) $request->input('body', ''),
        ];

        try {
            $service->sendInternalMail($user, $payload);
            $this->app->session()->flash('success', 'Interne Mail wurde gesendet.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Interne Mail konnte nicht gesendet werden.');
            $this->app->session()->flash('mail_old_recipient', $payload['recipient_email']);
            $this->app->session()->flash('mail_old_subject', $payload['subject']);
            $this->app->session()->flash('mail_old_body', $payload['body']);
        }

        $this->redirect('/mail');
    }
}
