<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\Database;
use DateInterval;
use DateTimeImmutable;
use PDO;
use RuntimeException;

final class LoginChallengeThrottleService
{
    public function __construct(
        private readonly App $app,
        private readonly ?DateTimeImmutable $currentTime = null
    ) {
    }

    public function ensureAllowed(int $challengeId, ?string $ipAddress): void
    {
        $state = $this->activeState($challengeId, $ipAddress, true);

        if ($state === null || ($state['locked'] ?? false) !== true) {
            return;
        }

        throw new RuntimeException($this->lockoutMessage((int) ($state['available_in_seconds'] ?? 0)));
    }

    public function recordFailure(int $challengeId, ?string $ipAddress): array
    {
        $normalizedIp = $this->normalizeIp($ipAddress);
        $now = $this->now();
        $existing = $this->activeState($challengeId, $normalizedIp, true);

        $failedAttempts = (int) ($existing['failed_attempts'] ?? 0) + 1;
        $windowStartedAt = $existing['window_started_at'] ?? $now;
        $lockedUntil = null;

        if ($failedAttempts >= $this->maxAttempts()) {
            $lockedUntil = $now->add(new DateInterval('PT' . $this->decaySeconds() . 'S'));
        }

        $this->upsertState(
            $this->throttleKey($challengeId, $normalizedIp),
            $challengeId,
            $normalizedIp,
            $failedAttempts,
            $windowStartedAt,
            $now,
            $lockedUntil
        );

        return [
            'failed_attempts' => $failedAttempts,
            'locked' => $lockedUntil instanceof DateTimeImmutable,
            'available_in_seconds' => $lockedUntil instanceof DateTimeImmutable
                ? max(1, $lockedUntil->getTimestamp() - $now->getTimestamp())
                : 0,
        ];
    }

    public function clear(int $challengeId, ?string $ipAddress): void
    {
        $statement = $this->pdo()->prepare(
            'DELETE FROM login_challenge_attempt_limits
             WHERE throttle_key = :throttle_key'
        );
        $statement->execute([
            'throttle_key' => $this->throttleKey($challengeId, $this->normalizeIp($ipAddress)),
        ]);
    }

    public function lockoutMessage(int $availableInSeconds): string
    {
        $availableInSeconds = max(1, $availableInSeconds);
        $minutes = (int) ceil($availableInSeconds / 60);

        if ($minutes <= 1) {
            return 'Zu viele falsche Anmeldecodes. Bitte in 1 Minute erneut versuchen.';
        }

        return sprintf('Zu viele falsche Anmeldecodes. Bitte in %d Minuten erneut versuchen.', $minutes);
    }

    private function activeState(int $challengeId, ?string $ipAddress, bool $clearExpired): ?array
    {
        if ($challengeId <= 0) {
            return null;
        }

        $normalizedIp = $this->normalizeIp($ipAddress);
        $statement = $this->pdo()->prepare(
            'SELECT challenge_id,
                    ip_address,
                    failed_attempts,
                    window_started_at,
                    last_attempted_at,
                    locked_until
             FROM login_challenge_attempt_limits
             WHERE throttle_key = :throttle_key
             LIMIT 1'
        );
        $statement->execute([
            'throttle_key' => $this->throttleKey($challengeId, $normalizedIp),
        ]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return null;
        }

        $now = $this->now();
        $windowStartedAt = $this->parseDateTime((string) ($row['window_started_at'] ?? ''));
        $lockedUntil = $this->parseDateTime((string) ($row['locked_until'] ?? ''));

        $windowExpired = $windowStartedAt === null
            || (($now->getTimestamp() - $windowStartedAt->getTimestamp()) >= $this->decaySeconds());
        $lockExpired = $lockedUntil !== null && $lockedUntil->getTimestamp() <= $now->getTimestamp();

        if ($windowExpired || $lockExpired) {
            if ($clearExpired) {
                $this->clear($challengeId, $normalizedIp);
            }

            return null;
        }

        return [
            'failed_attempts' => (int) ($row['failed_attempts'] ?? 0),
            'window_started_at' => $windowStartedAt,
            'locked_until' => $lockedUntil,
            'locked' => $lockedUntil !== null,
            'available_in_seconds' => $lockedUntil !== null
                ? max(1, $lockedUntil->getTimestamp() - $now->getTimestamp())
                : 0,
        ];
    }

    private function upsertState(
        string $throttleKey,
        int $challengeId,
        string $ipAddress,
        int $failedAttempts,
        DateTimeImmutable $windowStartedAt,
        DateTimeImmutable $lastAttemptedAt,
        ?DateTimeImmutable $lockedUntil
    ): void {
        $statement = $this->pdo()->prepare(
            'INSERT INTO login_challenge_attempt_limits (
                throttle_key,
                challenge_id,
                ip_address,
                failed_attempts,
                window_started_at,
                last_attempted_at,
                locked_until
            ) VALUES (
                :throttle_key,
                :challenge_id,
                :ip_address,
                :failed_attempts,
                :window_started_at,
                :last_attempted_at,
                :locked_until
            )
            ON DUPLICATE KEY UPDATE
                challenge_id = VALUES(challenge_id),
                ip_address = VALUES(ip_address),
                failed_attempts = VALUES(failed_attempts),
                window_started_at = VALUES(window_started_at),
                last_attempted_at = VALUES(last_attempted_at),
                locked_until = VALUES(locked_until)'
        );
        $statement->execute([
            'throttle_key' => $throttleKey,
            'challenge_id' => $challengeId,
            'ip_address' => $ipAddress,
            'failed_attempts' => $failedAttempts,
            'window_started_at' => $windowStartedAt->format('Y-m-d H:i:s'),
            'last_attempted_at' => $lastAttemptedAt->format('Y-m-d H:i:s'),
            'locked_until' => $lockedUntil?->format('Y-m-d H:i:s'),
        ]);
    }

    private function throttleKey(int $challengeId, string $ipAddress): string
    {
        return hash('sha256', $challengeId . '|' . $ipAddress);
    }

    private function normalizeIp(?string $ipAddress): string
    {
        $ipAddress = is_string($ipAddress) ? trim($ipAddress) : '';

        return $ipAddress !== '' ? $ipAddress : 'unknown';
    }

    private function parseDateTime(string $value): ?DateTimeImmutable
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return new DateTimeImmutable($value);
    }

    private function maxAttempts(): int
    {
        return max(1, (int) $this->app->config('auth.mfa.email_challenge.throttle.max_attempts', 3));
    }

    private function decaySeconds(): int
    {
        return max(60, (int) $this->app->config('auth.mfa.email_challenge.throttle.decay_seconds', 900));
    }

    private function now(): DateTimeImmutable
    {
        return $this->currentTime ?? new DateTimeImmutable('now');
    }

    private function pdo(): PDO
    {
        return Database::instance()->pdo();
    }
}
