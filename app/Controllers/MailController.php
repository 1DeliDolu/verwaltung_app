<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\VerifiedMiddleware;
use App\Services\MailService;

final class MailController extends Controller
{
    public function sendDemo(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        VerifiedMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $recipient = (string) $this->app->config('mail.test_recipient', 'admin@verwaltung.demo');
        $subject = 'Probe Mail aus Verwaltung App';
        $body = "Dies ist eine Testnachricht aus der Demo-Umgebung.\n\nVersendet am: " . date('Y-m-d H:i:s');

        try {
            (new MailService($this->app))->sendTestMessage($recipient, $subject, $body);
            $this->app->session()->flash('success', 'Testmail wurde an MailHog gesendet.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Testmail konnte nicht gesendet werden.');
        }

        $this->redirect('/dashboard');
    }
}
