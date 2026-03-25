<?php $title = 'User Audit'; ?>
<div class="hero">
    <p class="eyebrow">Admin</p>
    <h1 class="display-6 fw-semibold">User Management Audit</h1>
    <p class="lead">Alle Admin-Aktionen rund um Passwort-Reset und Bereichszuordnung werden hier als Audit-Trail angezeigt.</p>
</div>

<div class="card card-soft mb-4">
    <form method="GET" action="/users/audit" class="row g-3 align-items-end">
        <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold" for="search">Suche</label>
            <input
                class="form-control"
                id="search"
                name="search"
                value="<?= htmlspecialchars((string) ($filters['search'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Actor, Ziel, Abteilung oder Grund"
            >
        </div>
        <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold" for="action">Aktion</label>
            <select class="form-select" id="action" name="action">
                <option value="">Alle Aktionen</option>
                <?php foreach ($actionOptions as $actionKey => $actionLabel): ?>
                    <option value="<?= htmlspecialchars((string) $actionKey, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($filters['action'] ?? '') === (string) $actionKey ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $actionLabel, ENT_QUOTES, 'UTF-8') ?>
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
            <input
                class="form-control"
                id="date_from"
                name="date_from"
                type="date"
                value="<?= htmlspecialchars((string) ($filters['date_from'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>
        <div class="col-12 col-lg-6">
            <label class="form-label fw-semibold" for="date_to">Bis</label>
            <input
                class="form-control"
                id="date_to"
                name="date_to"
                type="date"
                value="<?= htmlspecialchars((string) ($filters['date_to'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>
        <div class="col-12 d-flex flex-wrap gap-2">
            <button class="btn px-4 py-2" type="submit">Filter anwenden</button>
            <button class="btn btn-outline-accent px-4 py-2" type="submit" name="format" value="csv">CSV Export</button>
            <a class="btn btn-outline-accent px-4 py-2" href="/users/audit">Zuruecksetzen</a>
        </div>
    </form>
</div>

<div class="card card-soft">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Audit Events</p>
            <h2 class="h4 mb-0"><?= count($events) ?> Eintraege</h2>
        </div>
        <div class="dashboard-role-badge">
            Nur fuer Admin sichtbar
        </div>
    </div>

    <?php if ($events === []): ?>
        <p class="muted mb-0">Noch keine passenden Audit-Eintraege gefunden.</p>
    <?php else: ?>
        <div class="d-grid gap-3">
            <?php foreach ($events as $event): ?>
                <article class="border rounded-4 p-4 bg-white">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                        <div>
                            <p class="eyebrow mb-1"><?= htmlspecialchars((string) ($event['action'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
                            <h3 class="h5 mb-1">
                                <?= htmlspecialchars((string) ($event['actor']['name'] ?? $event['actor']['email'] ?? 'Admin'), ENT_QUOTES, 'UTF-8') ?>
                                -> <?= htmlspecialchars((string) ($event['target_user']['email'] ?? 'Unbekannt'), ENT_QUOTES, 'UTF-8') ?>
                            </h3>
                            <p class="muted mb-0"><?= htmlspecialchars((string) ($event['timestamp'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="dashboard-role-badge">
                            <?= htmlspecialchars((string) strtoupper((string) ($event['outcome'] ?? 'success')), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </div>

                    <div class="row g-3 small">
                        <div class="col-12 col-lg-4">
                            <strong>Actor:</strong>
                            <?= htmlspecialchars((string) ($event['actor']['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="col-12 col-lg-4">
                            <strong>Ziel:</strong>
                            <?= htmlspecialchars((string) ($event['target_user']['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="col-12 col-lg-4">
                            <strong>Abteilung:</strong>
                            <?= htmlspecialchars((string) ($event['department']['name'] ?? $event['department']['slug'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="col-12 col-lg-4">
                            <strong>Membership:</strong>
                            <?= htmlspecialchars((string) ($event['metadata']['membership_role'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="col-12 col-lg-8">
                            <strong>Grund:</strong>
                            <?= htmlspecialchars((string) ($event['reason'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
