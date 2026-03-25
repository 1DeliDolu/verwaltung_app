<?php $title = 'Audit Dashboard'; ?>
<div class="hero">
    <p class="eyebrow">Admin</p>
    <h1 class="display-6 fw-semibold">Zentrales Audit Dashboard</h1>
    <p class="lead">User Management, Tasks, Mail und Calendar laufen hier als zentrale Audit-Ansicht zusammen.</p>
</div>

<div class="dashboard-stat-grid mb-4">
    <a class="dashboard-stat-tile text-decoration-none" href="/audit?source=admin_user">
        <span class="dashboard-stat-value"><?= htmlspecialchars((string) ($summary['admin_user'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
        <span class="dashboard-stat-label">User Management</span>
    </a>
    <a class="dashboard-stat-tile text-decoration-none" href="/audit?source=task">
        <span class="dashboard-stat-value"><?= htmlspecialchars((string) ($summary['task'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
        <span class="dashboard-stat-label">Tasks</span>
    </a>
    <a class="dashboard-stat-tile text-decoration-none" href="/audit?source=mail">
        <span class="dashboard-stat-value"><?= htmlspecialchars((string) ($summary['mail'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
        <span class="dashboard-stat-label">Mail</span>
    </a>
    <a class="dashboard-stat-tile text-decoration-none" href="/audit?source=calendar">
        <span class="dashboard-stat-value"><?= htmlspecialchars((string) ($summary['calendar'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
        <span class="dashboard-stat-label">Calendar</span>
    </a>
</div>

<div class="card card-soft mb-4">
    <form method="GET" action="/audit" class="row g-3 align-items-end">
        <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold" for="search">Suche</label>
            <input class="form-control" id="search" name="search" value="<?= htmlspecialchars((string) ($filters['search'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Actor, subject, context">
        </div>
        <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold" for="source">Quelle</label>
            <select class="form-select" id="source" name="source">
                <option value="">Alle Quellen</option>
                <?php foreach ($sourceOptions as $sourceKey => $sourceLabel): ?>
                    <option value="<?= htmlspecialchars((string) $sourceKey, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($filters['source'] ?? '') === (string) $sourceKey ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $sourceLabel, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold" for="outcome">Outcome</label>
            <select class="form-select" id="outcome" name="outcome">
                <option value="">Alle Outcomes</option>
                <?php foreach ($outcomeOptions as $outcomeKey => $outcomeLabel): ?>
                    <option value="<?= htmlspecialchars((string) $outcomeKey, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($filters['outcome'] ?? '') === (string) $outcomeKey ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $outcomeLabel, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-lg-6">
            <label class="form-label fw-semibold" for="date_from">Von</label>
            <input class="form-control" id="date_from" name="date_from" type="date" value="<?= htmlspecialchars((string) ($filters['date_from'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-12 col-lg-6">
            <label class="form-label fw-semibold" for="date_to">Bis</label>
            <input class="form-control" id="date_to" name="date_to" type="date" value="<?= htmlspecialchars((string) ($filters['date_to'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-12 d-flex flex-wrap gap-2">
            <button class="btn px-4 py-2" type="submit">Filter anwenden</button>
            <button class="btn btn-outline-accent px-4 py-2" type="submit" name="format" value="csv">CSV Export</button>
            <a class="btn btn-outline-accent px-4 py-2" href="/audit">Zuruecksetzen</a>
        </div>
    </form>
</div>

<div class="card card-soft">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Audit Stream</p>
            <h2 class="h4 mb-0"><?= count($events) ?> Eintraege</h2>
        </div>
        <div class="dashboard-role-badge">
            Nur fuer Admin sichtbar
        </div>
    </div>

    <?php if ($events === []): ?>
        <p class="muted mb-0">Keine Audit-Eintraege fuer den aktuellen Filter gefunden.</p>
    <?php else: ?>
        <div class="d-grid gap-3">
            <?php foreach ($events as $event): ?>
                <article class="border rounded-4 p-4 bg-white">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                        <div>
                            <p class="eyebrow mb-1"><?= htmlspecialchars((string) ($event['source_label'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
                            <h3 class="h5 mb-1"><?= htmlspecialchars((string) ($event['subject'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="muted mb-0"><?= htmlspecialchars((string) ($event['timestamp'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="dashboard-role-badge">
                            <?= htmlspecialchars((string) strtoupper((string) ($event['outcome'] ?? 'success')), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </div>

                    <div class="row g-3 small">
                        <div class="col-12 col-lg-3">
                            <strong>Quelle:</strong>
                            <?= htmlspecialchars((string) ($event['source_label'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="col-12 col-lg-3">
                            <strong>Aktion:</strong>
                            <?= htmlspecialchars((string) ($event['action'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="col-12 col-lg-3">
                            <strong>Actor:</strong>
                            <?= htmlspecialchars((string) ($event['actor_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="col-12 col-lg-3">
                            <strong>Kontext:</strong>
                            <?= htmlspecialchars((string) ($event['context'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="col-12 col-lg-9">
                            <strong>Grund:</strong>
                            <?= htmlspecialchars((string) ($event['reason'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="col-12 col-lg-3">
                            <a class="btn btn-outline-accent btn-sm" href="<?= htmlspecialchars((string) ($event['detail_url'] ?? '/audit'), ENT_QUOTES, 'UTF-8') ?>">Detailansicht</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
