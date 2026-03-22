<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
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

    public function recipientDirectory(): array
    {
        return User::internalDirectory();
    }

    public function sendInternalMail(array $sender, array $input): void
    {
        $recipient = trim((string) ($input['recipient_email'] ?? ''));
        $subject = trim((string) ($input['subject'] ?? ''));
        $body = trim((string) ($input['body'] ?? ''));

        if ($recipient === '' || $subject === '' || $body === '') {
            throw new RuntimeException('All mail fields are required.');
        }

        (new MailService($this->app))->sendMessage(
            $recipient,
            $subject,
            $body,
            (string) $sender['email'],
            (string) $sender['name']
        );
    }

    public function mailbox(array $user): array
    {
        return (new MailService($this->app))->mailboxFor((string) $user['email']);
    }

    public function inboxCount(array $user): int
    {
        return count($this->mailbox($user)['inbox']);
    }
}
