<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;

final class AuditLogService
{
    public function __construct(
        private readonly App $app,
        private readonly ?string $logPath = null
    ) {
    }

    public function recordPersonnelDocumentEvent(string $action, array $context = []): void
    {
        $this->writeAuditEntry([
            'timestamp' => date('c'),
            'event' => 'personnel_document_access',
            'action' => $action,
            'outcome' => (string) ($context['outcome'] ?? 'success'),
            'reason' => $this->stringOrNull($context['reason'] ?? null),
            'actor' => $this->normalizeActor($context['actor'] ?? null),
            'department' => $this->normalizeDepartment($context['department'] ?? null),
            'employee' => $this->normalizeEmployee($context['employee'] ?? null),
            'document' => $this->normalizeDocument($context['document'] ?? null),
            'request' => $this->normalizeRequest(),
        ], $this->logFilePath());
    }

    public function recordAdminUserEvent(string $action, array $context = []): void
    {
        $this->writeAuditEntry([
            'timestamp' => date('c'),
            'event' => 'admin_user_management',
            'action' => $action,
            'outcome' => (string) ($context['outcome'] ?? 'success'),
            'reason' => $this->stringOrNull($context['reason'] ?? null),
            'actor' => $this->normalizeActor($context['actor'] ?? null),
            'target_user' => $this->normalizeActor($context['target_user'] ?? null),
            'department' => $this->normalizeDepartment($context['department'] ?? null),
            'metadata' => $this->normalizeMetadata($context['metadata'] ?? null),
            'request' => $this->normalizeRequest(),
        ], $this->adminLogFilePath());
    }

    public function logFilePath(): string
    {
        return $this->logPath ?? BASE_PATH . '/storage/logs/personnel-document-access.log';
    }

    public function adminLogFilePath(): string
    {
        return BASE_PATH . '/storage/logs/admin-user-management.log';
    }

    private function normalizeActor(mixed $actor): ?array
    {
        if (!is_array($actor)) {
            return null;
        }

        return array_filter([
            'id' => isset($actor['id']) ? (int) $actor['id'] : null,
            'name' => $this->stringOrNull($actor['name'] ?? null),
            'email' => $this->stringOrNull($actor['email'] ?? null),
            'role_name' => $this->stringOrNull($actor['role_name'] ?? null),
        ], static fn (mixed $value): bool => $value !== null);
    }

    private function normalizeDepartment(mixed $department): ?array
    {
        if (!is_array($department)) {
            return null;
        }

        return array_filter([
            'id' => isset($department['id']) ? (int) $department['id'] : null,
            'slug' => $this->stringOrNull($department['slug'] ?? null),
            'name' => $this->stringOrNull($department['name'] ?? null),
        ], static fn (mixed $value): bool => $value !== null);
    }

    private function normalizeEmployee(mixed $employee): ?array
    {
        if (is_int($employee)) {
            return ['id' => $employee];
        }

        if (!is_array($employee)) {
            return null;
        }

        return array_filter([
            'id' => isset($employee['id']) ? (int) $employee['id'] : null,
            'employee_number' => $this->stringOrNull($employee['employee_number'] ?? null),
            'full_name' => $this->stringOrNull($employee['full_name'] ?? $employee['employee_name'] ?? null),
        ], static fn (mixed $value): bool => $value !== null);
    }

    private function normalizeDocument(mixed $document): ?array
    {
        if (is_int($document)) {
            return ['id' => $document];
        }

        if (!is_array($document)) {
            return null;
        }

        return array_filter([
            'id' => isset($document['id']) ? (int) $document['id'] : null,
            'employee_id' => isset($document['employee_id']) ? (int) $document['employee_id'] : null,
            'original_name' => $this->stringOrNull($document['original_name'] ?? null),
            'stored_name' => $this->stringOrNull($document['stored_name'] ?? null),
            'file_path' => $this->stringOrNull($document['file_path'] ?? null),
            'mime_type' => $this->stringOrNull($document['mime_type'] ?? null),
            'file_size' => isset($document['file_size']) ? (int) $document['file_size'] : null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    private function normalizeRequest(): array
    {
        return array_filter([
            'path' => $this->stringOrNull($this->app->request()->path()),
            'ip' => $this->stringOrNull($this->app->request()->ip()),
            'user_agent' => $this->stringOrNull($this->app->request()->userAgent()),
        ], static fn (mixed $value): bool => $value !== null);
    }

    private function normalizeMetadata(mixed $metadata): ?array
    {
        if (!is_array($metadata)) {
            return null;
        }

        return array_filter([
            'membership_role' => $this->stringOrNull($metadata['membership_role'] ?? null),
            'target_email' => $this->stringOrNull($metadata['target_email'] ?? null),
            'reset_to_default_password' => isset($metadata['reset_to_default_password'])
                ? (bool) $metadata['reset_to_default_password']
                : null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    private function writeAuditEntry(array $payload, string $logPath): void
    {
        $payload = array_filter($payload, static fn (mixed $value): bool => $value !== null && $value !== []);
        $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES);

        if (!is_string($encoded) || $encoded === '') {
            error_log('Audit log payload could not be encoded.');
            return;
        }

        $directory = dirname($logPath);

        if (!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory)) {
            error_log('Audit log directory could not be created: ' . $directory);
            return;
        }

        if (@file_put_contents($logPath, $encoded . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            error_log('Audit log entry could not be written: ' . $logPath);
        }
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
