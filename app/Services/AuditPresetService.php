<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Models\AuditFilterPreset;
use RuntimeException;

final class AuditPresetService
{
    private const SOURCE_LABELS = [
        'admin_user' => 'User Management',
        'task' => 'Tasks',
        'mail' => 'Mail',
        'calendar' => 'Calendar',
    ];

    private const OUTCOME_LABELS = [
        'success' => 'Erfolg',
        'failure' => 'Fehler',
    ];

    public function __construct(private readonly App $app)
    {
    }

    public function extractFilters(array $input): array
    {
        return [
            'source' => trim((string) ($input['source'] ?? '')),
            'search' => trim((string) ($input['search'] ?? '')),
            'outcome' => trim((string) ($input['outcome'] ?? '')),
            'date_from' => trim((string) ($input['date_from'] ?? '')),
            'date_to' => trim((string) ($input['date_to'] ?? '')),
        ];
    }

    public function presetsForUser(array $user): array
    {
        $this->assertAdmin($user);
        $presets = AuditFilterPreset::forUser((int) $user['id']);

        foreach ($presets as &$preset) {
            $preset['url'] = $this->dashboardUrl($this->extractFilters($preset));
            $preset['summary'] = $this->presetSummary($preset);
        }
        unset($preset);

        return $presets;
    }

    public function savePreset(array $user, array $input): int
    {
        $this->assertAdmin($user);

        $name = trim((string) ($input['name'] ?? ''));
        $filters = $this->extractFilters($input);

        if ($name === '') {
            throw new RuntimeException('Preset-Name ist erforderlich.');
        }

        if (mb_strlen($name) > 120) {
            throw new RuntimeException('Preset-Name ist zu lang.');
        }

        if (!$this->hasActiveFilters($filters)) {
            throw new RuntimeException('Mindestens ein Filter muss gesetzt sein.');
        }

        $this->assertValidFilters($filters);

        return AuditFilterPreset::upsert([
            'user_id' => (int) $user['id'],
            'name' => $name,
            'source' => $filters['source'] === '' ? null : $filters['source'],
            'search' => $filters['search'] === '' ? null : $filters['search'],
            'outcome' => $filters['outcome'] === '' ? null : $filters['outcome'],
            'date_from' => $filters['date_from'] === '' ? null : $filters['date_from'],
            'date_to' => $filters['date_to'] === '' ? null : $filters['date_to'],
        ]);
    }

    public function deletePreset(array $user, int $presetId): void
    {
        $this->assertAdmin($user);

        if ($presetId <= 0 || !AuditFilterPreset::deleteForUser((int) $user['id'], $presetId)) {
            throw new RuntimeException('Preset konnte nicht geloescht werden.');
        }
    }

    public function hasActiveFilters(array $filters): bool
    {
        foreach ($this->extractFilters($filters) as $value) {
            if ($value !== '') {
                return true;
            }
        }

        return false;
    }

    private function presetSummary(array $preset): array
    {
        $summary = [];
        $source = trim((string) ($preset['source'] ?? ''));
        $outcome = trim((string) ($preset['outcome'] ?? ''));
        $search = trim((string) ($preset['search'] ?? ''));
        $dateFrom = trim((string) ($preset['date_from'] ?? ''));
        $dateTo = trim((string) ($preset['date_to'] ?? ''));

        if ($source !== '') {
            $summary[] = 'Quelle: ' . (self::SOURCE_LABELS[$source] ?? $source);
        }

        if ($outcome !== '') {
            $summary[] = 'Outcome: ' . (self::OUTCOME_LABELS[$outcome] ?? $outcome);
        }

        if ($search !== '') {
            $summary[] = 'Suche: ' . $search;
        }

        if ($dateFrom !== '' && $dateTo !== '') {
            $summary[] = 'Zeitraum: ' . $dateFrom . ' bis ' . $dateTo;
        } elseif ($dateFrom !== '') {
            $summary[] = 'Von: ' . $dateFrom;
        } elseif ($dateTo !== '') {
            $summary[] = 'Bis: ' . $dateTo;
        }

        return $summary;
    }

    private function assertValidFilters(array $filters): void
    {
        if ($filters['source'] !== '' && !array_key_exists($filters['source'], self::SOURCE_LABELS)) {
            throw new RuntimeException('Filter-Quelle ist ungueltig.');
        }

        if ($filters['outcome'] !== '' && !array_key_exists($filters['outcome'], self::OUTCOME_LABELS)) {
            throw new RuntimeException('Filter-Outcome ist ungueltig.');
        }

        if ($filters['search'] !== '' && mb_strlen($filters['search']) > 255) {
            throw new RuntimeException('Suchbegriff ist zu lang.');
        }

        if ($filters['date_from'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_from'])) {
            throw new RuntimeException('Startdatum ist ungueltig.');
        }

        if ($filters['date_to'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_to'])) {
            throw new RuntimeException('Enddatum ist ungueltig.');
        }

        if ($filters['date_from'] !== '' && $filters['date_to'] !== '' && $filters['date_from'] > $filters['date_to']) {
            throw new RuntimeException('Startdatum darf nicht nach dem Enddatum liegen.');
        }
    }

    private function dashboardUrl(array $params): string
    {
        $filtered = array_filter($params, static fn (mixed $value): bool => $value !== null && $value !== '');

        if ($filtered === []) {
            return '/audit';
        }

        return '/audit?' . http_build_query($filtered);
    }

    private function assertAdmin(array $user): void
    {
        if ((string) ($user['role_name'] ?? '') !== 'admin') {
            throw new RuntimeException('Nur Admins duerfen Audit-Presets verwalten.');
        }
    }
}
