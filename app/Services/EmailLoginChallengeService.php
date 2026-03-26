<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Models\LoginEmailChallenge;
use App\Models\User;
use DateInterval;
use DateTimeImmutable;
use RuntimeException;

final class EmailLoginChallengeService
{
    public const INVALID_CODE_MESSAGE = 'Der Anmeldecode ist ungueltig oder abgelaufen.';

    public function __construct(
        private readonly App $app,
        private readonly ?DateTimeImmutable $currentTime = null
    ) {
    }

    public function requiresChallenge(array $user): bool
    {
        $role = trim((string) ($user['role_name'] ?? ''));

        return $role !== ''
            && in_array($role, $this->enabledRoles(), true);
    }

    public function begin(array $user, ?string $ipAddress, ?string $userAgent): array
    {
        $userId = (int) ($user['id'] ?? 0);

        if ($userId <= 0 || trim((string) ($user['email'] ?? '')) === '') {
            throw new RuntimeException('Login challenge user is invalid.');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $issuedAt = $this->now();
        $expiresAt = $issuedAt->add(new DateInterval('PT' . $this->expireSeconds() . 'S'));

        LoginEmailChallenge::invalidateUnusedForUser($userId, $issuedAt->format('Y-m-d H:i:s'));
        $challengeId = LoginEmailChallenge::create([
            'user_id' => $userId,
            'code_hash' => hash('sha256', $code),
            'requested_ip' => $this->normalizeIp($ipAddress),
            'requested_user_agent' => $this->normalizeUserAgent($userAgent),
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);

        $subject = 'Anmeldecode fuer Verwaltung App';
        $body = implode("\n\n", [
            'Hallo ' . (string) ($user['name'] ?? 'Teammitglied') . ',',
            'dein Anmeldecode fuer Verwaltung App lautet:',
            $code,
            sprintf('Der Code ist %d Minuten gueltig und kann nur einmal verwendet werden.', (int) ceil($this->expireSeconds() / 60)),
            'Wenn du diese Anmeldung nicht gestartet hast, aendere bitte dein Passwort.',
        ]);

        (new MailService($this->app))->sendMessage((string) $user['email'], $subject, $body);

        return [
            'challenge_id' => $challengeId,
            'user_id' => $userId,
            'email' => (string) $user['email'],
            'ip_address' => $this->normalizeIp($ipAddress),
        ];
    }

    public function verify(array $pendingChallenge, string $plainCode): array
    {
        $challengeId = (int) ($pendingChallenge['challenge_id'] ?? 0);
        $userId = (int) ($pendingChallenge['user_id'] ?? 0);
        $ipAddress = $pendingChallenge['ip_address'] ?? null;
        $plainCode = trim($plainCode);
        $throttle = new LoginChallengeThrottleService($this->app, $this->currentTime);

        if ($challengeId <= 0 || $userId <= 0 || $plainCode === '') {
            throw new RuntimeException(self::INVALID_CODE_MESSAGE);
        }

        $throttle->ensureAllowed($challengeId, is_string($ipAddress) ? $ipAddress : null);

        $challenge = LoginEmailChallenge::findActiveById($challengeId);

        if ($challenge === null || (int) ($challenge['user_id'] ?? 0) !== $userId) {
            throw new RuntimeException(self::INVALID_CODE_MESSAGE);
        }

        $expiresAt = new DateTimeImmutable((string) ($challenge['expires_at'] ?? ''));

        if ($expiresAt <= $this->now()) {
            LoginEmailChallenge::markConsumed($challengeId, $this->now()->format('Y-m-d H:i:s'));
            throw new RuntimeException(self::INVALID_CODE_MESSAGE);
        }

        if (!hash_equals((string) ($challenge['code_hash'] ?? ''), hash('sha256', $plainCode))) {
            $failure = $throttle->recordFailure($challengeId, is_string($ipAddress) ? $ipAddress : null);

            if (($failure['locked'] ?? false) === true) {
                throw new RuntimeException($throttle->lockoutMessage((int) ($failure['available_in_seconds'] ?? 0)));
            }

            throw new RuntimeException(self::INVALID_CODE_MESSAGE);
        }

        $throttle->clear($challengeId, is_string($ipAddress) ? $ipAddress : null);
        LoginEmailChallenge::markConsumed($challengeId, $this->now()->format('Y-m-d H:i:s'));
        $user = User::findById($userId);

        if ($user === null) {
            throw new RuntimeException(self::INVALID_CODE_MESSAGE);
        }

        return $user;
    }

    private function enabledRoles(): array
    {
        $roles = (array) $this->app->config('auth.mfa.email_challenge.enabled_roles', []);

        return array_values(array_filter(array_map(
            static fn (string $role): string => trim($role),
            $roles
        )));
    }

    private function expireSeconds(): int
    {
        return max(60, (int) $this->app->config('auth.mfa.email_challenge.expire_seconds', 600));
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

    private function now(): DateTimeImmutable
    {
        return $this->currentTime ?? new DateTimeImmutable('now');
    }
}
