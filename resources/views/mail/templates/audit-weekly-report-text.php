Audit Wochenreport

Zeitraum: <?= (string) ($window['label'] ?? '') . PHP_EOL ?>
Erstellt am: <?= (string) ($generated_at ?? '') . PHP_EOL ?>
Empfaenger: <?= htmlspecialchars(implode(', ', (array) ($recipients ?? [])), ENT_QUOTES, 'UTF-8') . PHP_EOL ?>

Gesamtueberblick
- Events gesamt: <?= (string) (($stats['total'] ?? 0)) . PHP_EOL ?>
- Erfolg: <?= (string) (($stats['success'] ?? 0)) . PHP_EOL ?>
- Fehler: <?= (string) (($stats['failure'] ?? 0)) . PHP_EOL ?>
- Failure Rate: <?= (string) (($stats['failure_rate'] ?? 0)) ?>%

Quellen
<?php foreach ((array) ($source_summary ?? []) as $source): ?>
- <?= (string) ($source['label'] ?? '') ?>: <?= (string) ($source['count'] ?? 0) . PHP_EOL ?>
<?php endforeach; ?>

Aktivste Nutzer
<?php if (($top_actors ?? []) === []): ?>
- Keine Nutzerdaten im Berichtfenster.
<?php else: ?>
<?php foreach ((array) ($top_actors ?? []) as $actor): ?>
- <?= (string) ($actor['email'] ?? '') ?>: <?= (string) ($actor['total'] ?? 0) ?> Events, <?= (string) ($actor['failure'] ?? 0) ?> Fehler
<?php endforeach; ?>
<?php endif; ?>

Letzte Fehler
<?php if (($recent_failures ?? []) === []): ?>
- Keine Fehler im Berichtfenster.
<?php else: ?>
<?php foreach ((array) ($recent_failures ?? []) as $failure): ?>
- <?= (string) ($failure['timestamp'] ?? '') ?> | <?= (string) ($failure['source_label'] ?? '') ?> | <?= (string) ($failure['action'] ?? '') ?> | <?= (string) ($failure['subject'] ?? '') ?> | <?= (string) ($failure['reason'] ?? '-') ?>
<?php endforeach; ?>
<?php endif; ?>

Anhang
- <?= (string) ($csv_name ?? '') ?>
