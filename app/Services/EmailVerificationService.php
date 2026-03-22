<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Models\User;
use RuntimeException;

final class EmailVerificationService
{
    public function __construct(private readonly App $app)
    {
    }

    public function sendVerificationMail(array $user): void
    {
        if (($user['email_verified_at'] ?? null) !== null) {
            return;
        }

        $token = bin2hex(random_bytes(24));
        User::setVerificationToken((int) $user['id'], $token);

        $verificationUrl = sprintf(
            '%s/email/verify/%d/%s',
            $this->baseUrl(),
            (int) $user['id'],
            $token
        );

        $subject = 'Bitte bestaetige deine E-Mail-Adresse';
        $body = implode("\n\n", [
            'Hallo ' . $user['name'] . ',',
            'bitte bestaetige deine E-Mail-Adresse fuer Verwaltung App ueber folgenden Link:',
            $verificationUrl,
            'Wenn du diese Anfrage nicht erwartet hast, kannst du diese Nachricht ignorieren.',
        ]);

        (new MailService($this->app))->sendMessage(
            (string) $user['email'],
            $subject,
            $body
        );
    }

    public function verify(int $userId, string $plainToken): void
    {
        $user = User::findForVerification($userId);

        if ($user === null) {
            throw new RuntimeException('Verification user not found.');
        }

        if (($user['email_verified_at'] ?? null) !== null) {
            return;
        }

        $storedHash = (string) ($user['email_verification_token'] ?? '');

        if ($storedHash === '' || !hash_equals($storedHash, hash('sha256', $plainToken))) {
            throw new RuntimeException('Invalid verification token.');
        }

        User::verifyEmail($userId);
    }

    private function baseUrl(): string
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8080';

        return $scheme . '://' . $host;
    }
}
