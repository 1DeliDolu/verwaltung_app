<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Models\PasswordResetToken;
use App\Models\User;
use DateInterval;
use DateTimeImmutable;
use RuntimeException;

final class PasswordResetService
{
    public const INVALID_REQUEST_MESSAGE = 'Bitte gib eine gueltige E-Mail-Adresse ein.';
    public const INVALID_TOKEN_MESSAGE = 'Der Reset-Link ist ungueltig oder abgelaufen.';

    public function __construct(
        private readonly App $app,
        private readonly ?DateTimeImmutable $currentTime = null
    ) {
    }

    public function requestLink(string $email, ?string $ipAddress, ?string $userAgent): void
    {
        $email = $this->normalizeEmail($email);

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException(self::INVALID_REQUEST_MESSAGE);
        }

        $user = User::findByEmail($email);

        if ($user === null) {
            return;
        }

        $token = bin2hex(random_bytes(32));
        $issuedAt = $this->now();
        $expiresAt = $issuedAt->add(new DateInterval('PT' . $this->expireSeconds() . 'S'));

        PasswordResetToken::invalidateUnusedForUser((int) $user['id'], $issuedAt->format('Y-m-d H:i:s'));
        PasswordResetToken::create([
            'user_id' => (int) $user['id'],
            'token_hash' => hash('sha256', $token),
            'requested_ip' => $this->normalizeIp($ipAddress),
            'requested_user_agent' => $this->normalizeUserAgent($userAgent),
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);

        $resetUrl = sprintf('%s/password/reset/%s', $this->baseUrl(), $token);
        $subject = 'Passwort zuruecksetzen';
        $body = implode("\n\n", [
            'Hallo ' . (string) $user['name'] . ',',
            'du kannst dein Passwort fuer Verwaltung App ueber folgenden Link neu setzen:',
            $resetUrl,
            sprintf('Der Link ist %d Minuten gueltig und kann nur einmal verwendet werden.', (int) ceil($this->expireSeconds() / 60)),
            'Wenn du diese Anfrage nicht gestartet hast, kannst du diese Nachricht ignorieren.',
        ]);

        (new MailService($this->app))->sendMessage((string) $user['email'], $subject, $body);
    }

    public function previewToken(string $plainToken): array
    {
        $record = $this->activeToken($plainToken);

        if ($record === null) {
            throw new RuntimeException(self::INVALID_TOKEN_MESSAGE);
        }

        return [
            'email' => (string) ($record['email'] ?? ''),
            'name' => (string) ($record['name'] ?? ''),
        ];
    }

    public function resetPassword(string $plainToken, string $password, string $passwordConfirmation): void
    {
        if ($password === '' || $passwordConfirmation === '') {
            throw new RuntimeException('Neue Passwortfelder sind erforderlich.');
        }

        if ($password !== $passwordConfirmation) {
            throw new RuntimeException('Passwortbestaetigung stimmt nicht ueberein.');
        }

        $record = $this->activeToken($plainToken);

        if ($record === null) {
            throw new RuntimeException(self::INVALID_TOKEN_MESSAGE);
        }

        $auth = new AuthService($this->app);
        $auth->assertPasswordStrength(
            $password,
            (string) ($record['email'] ?? ''),
            (string) ($record['name'] ?? '')
        );

        if (password_verify($password, (string) ($record['password_hash'] ?? ''))) {
            throw new RuntimeException('Neues Passwort muss sich vom aktuellen Passwort unterscheiden.');
        }

        User::updatePassword((int) $record['user_id'], password_hash($password, PASSWORD_DEFAULT));
        PasswordResetToken::invalidateUnusedForUser(
            (int) $record['user_id'],
            $this->now()->format('Y-m-d H:i:s')
        );
    }

    private function activeToken(string $plainToken): ?array
    {
        $plainToken = trim($plainToken);

        if ($plainToken === '') {
            return null;
        }

        $record = PasswordResetToken::findActiveByTokenHash(hash('sha256', $plainToken));

        if ($record === null) {
            return null;
        }

        $expiresAt = new DateTimeImmutable((string) ($record['expires_at'] ?? ''));

        if ($expiresAt <= $this->now()) {
            PasswordResetToken::markUsed((int) $record['id'], $this->now()->format('Y-m-d H:i:s'));

            return null;
        }

        return $record;
    }

    private function expireSeconds(): int
    {
        return max(300, (int) $this->app->config('auth.password_reset.expire_seconds', 3600));
    }

    private function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    private function normalizeIp(?string $ipAddress): string
    {
        $ipAddress = is_string($ipAddress) ? trim($ipAddress) : '';

        return $ipAddress !== '' ? $ipAddress : 'unknown';
    }

    private function normalizeUserAgent(?string $userAgent): ?string
    {
        $userAgent = is_string($userAgent) ? trim($userAgent) : '';

        return $userAgent !== '' ? mb_substr($userAgent, 0, 255) : null;
    }

    private function baseUrl(): string
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8080';

        return $scheme . '://' . $host;
    }

    private function now(): DateTimeImmutable
    {
        return $this->currentTime ?? new DateTimeImmutable('now');
    }
}
