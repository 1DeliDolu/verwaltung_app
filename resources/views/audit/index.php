<?php $title = 'Audit Dashboard'; ?>
<div class="hero">
    <p class="eyebrow">Admin</p>
    <h1 class="display-6 fw-semibold">Zentrales Audit Dashboard</h1>
    <p class="lead">User Management, Tasks, Mail und Calendar laufen hier als zentrale Audit-Ansicht zusammen.</p>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="dashboard-stat-grid mb-4">
    <a class="dashboard-stat-tile text-decoration-none" href="<?= htmlspecialchars((string) ($summary['admin_user']['url'] ?? '/audit'), ENT_QUOTES, 'UTF-8') ?>">
        <span class="dashboard-stat-value"><?= htmlspecialchars((string) ($summary['admin_user']['count'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
        <span class="dashboard-stat-label">User Management</span>
    </a>
    <a class="dashboard-stat-tile text-decoration-none" href="<?= htmlspecialchars((string) ($summary['task']['url'] ?? '/audit'), ENT_QUOTES, 'UTF-8') ?>">
        <span class="dashboard-stat-value"><?= htmlspecialchars((string) ($summary['task']['count'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
        <span class="dashboard-stat-label">Tasks</span>
    </a>
    <a class="dashboard-stat-tile text-decoration-none" href="<?= htmlspecialchars((string) ($summary['mail']['url'] ?? '/audit'), ENT_QUOTES, 'UTF-8') ?>">
        <span class="dashboard-stat-value"><?= htmlspecialchars((string) ($summary['mail']['count'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
        <span class="dashboard-stat-label">Mail</span>
    </a>
    <a class="dashboard-stat-tile text-decoration-none" href="<?= htmlspecialchars((string) ($summary['calendar']['url'] ?? '/audit'), ENT_QUOTES, 'UTF-8') ?>">
        <span class="dashboard-stat-value"><?= htmlspecialchars((string) ($summary['calendar']['count'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
        <span class="dashboard-stat-label">Calendar</span>
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-5">
        <div class="card card-soft h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="eyebrow mb-1">Aktivitaet</p>
                    <h2 class="h4 mb-0">Letzte 7 Tage</h2>
                </div>
                <span class="dashboard-role-badge">Trend</span>
            </div>

            <?php if (($trend ?? []) === []): ?>
                <p class="muted mb-0">Keine Tagesdaten fuer den aktuellen Filter.</p>
            <?php else: ?>
                <div class="d-grid gap-3">
                    <?php $trendMax = max(array_map(static fn (array $day): int => (int) $day['total'], $trend)); ?>
                    <?php foreach ($trend as $day): ?>
                        <?php $width = $trendMax > 0 ? max(8, (int) round(((int) $day['total'] / $trendMax) * 100)) : 8; ?>
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1 small">
                                <strong><a class="text-decoration-none text-reset" href="/audit?date_from=<?= urlencode((string) $day['date']) ?>&date_to=<?= urlencode((string) $day['date']) ?>"><?= htmlspecialchars((string) $day['date'], ENT_QUOTES, 'UTF-8') ?></a></strong>
                                <span><?= htmlspecialchars((string) $day['total'], ENT_QUOTES, 'UTF-8') ?> Events</span>
                            </div>
                            <div class="progress" role="progressbar" aria-valuenow="<?= htmlspecialchars((string) $day['total'], ENT_QUOTES, 'UTF-8') ?>" aria-valuemin="0" aria-valuemax="<?= htmlspecialchars((string) $trendMax, ENT_QUOTES, 'UTF-8') ?>">
                                <div class="progress-bar bg-danger" style="width: <?= htmlspecialchars((string) $width, ENT_QUOTES, 'UTF-8') ?>%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1 small text-secondary">
                                <span>Erfolg: <?= htmlspecialchars((string) $day['success'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span>Fehler: <?= htmlspecialchars((string) $day['failure'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-12 col-xl-7">
        <div class="card card-soft h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="eyebrow mb-1">Verteilung</p>
                    <h2 class="h4 mb-0">Top Aktionen nach Quelle</h2>
                </div>
                <span class="dashboard-role-badge">Breakdown</span>
            </div>

            <?php if (($actionBreakdown ?? []) === []): ?>
                <p class="muted mb-0">Keine Aktionsdaten fuer den aktuellen Filter.</p>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($actionBreakdown as $sourceKey => $source): ?>
                        <div class="col-12 col-lg-6">
                            <article class="border rounded-4 p-3 bg-white h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <strong><a class="text-decoration-none text-reset" href="/audit?source=<?= urlencode((string) $sourceKey) ?>"><?= htmlspecialchars((string) $source['label'], ENT_QUOTES, 'UTF-8') ?></a></strong>
                                    <span class="badge text-bg-light rounded-pill"><?= htmlspecialchars((string) $source['total'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="d-grid gap-2">
                                    <?php $sourceMax = max($source['actions'] !== [] ? array_values($source['actions']) : [1]); ?>
                                    <?php foreach ($source['actions'] as $action => $count): ?>
                                        <?php $actionWidth = $sourceMax > 0 ? max(12, (int) round(((int) $count / $sourceMax) * 100)) : 12; ?>
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                                <span><a class="text-decoration-none text-reset" href="/audit?source=<?= urlencode((string) $sourceKey) ?>&search=<?= urlencode((string) $action) ?>"><?= htmlspecialchars((string) $action, ENT_QUOTES, 'UTF-8') ?></a></span>
                                                <span><?= htmlspecialchars((string) $count, ENT_QUOTES, 'UTF-8') ?></span>
                                            </div>
                                            <div class="progress" role="progressbar" aria-valuenow="<?= htmlspecialchars((string) $count, ENT_QUOTES, 'UTF-8') ?>" aria-valuemin="0" aria-valuemax="<?= htmlspecialchars((string) $sourceMax, ENT_QUOTES, 'UTF-8') ?>">
                                                <div class="progress-bar bg-secondary" style="width: <?= htmlspecialchars((string) $actionWidth, ENT_QUOTES, 'UTF-8') ?>%"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-6">
        <div class="card card-soft h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="eyebrow mb-1">Akteure</p>
                    <h2 class="h4 mb-0">Aktivste Nutzer</h2>
                </div>
                <span class="dashboard-role-badge">Top 8</span>
            </div>

            <?php if (($topActors ?? []) === []): ?>
                <p class="muted mb-0">Keine Actor-Daten fuer den aktuellen Filter.</p>
            <?php else: ?>
                <div class="d-grid gap-3">
                    <?php $actorMax = max(array_map(static fn (array $actor): int => (int) $actor['total'], $topActors)); ?>
                    <?php foreach ($topActors as $actor): ?>
                        <?php $actorWidth = $actorMax > 0 ? max(10, (int) round(((int) $actor['total'] / $actorMax) * 100)) : 10; ?>
                        <article class="border rounded-4 p-3 bg-white">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div>
                                    <strong><a class="text-decoration-none text-reset" href="/audit?search=<?= urlencode((string) $actor['email']) ?>"><?= htmlspecialchars((string) $actor['email'], ENT_QUOTES, 'UTF-8') ?></a></strong>
                                    <div class="small text-secondary">
                                        <?= htmlspecialchars(implode(', ', array_keys((array) ($actor['sources'] ?? []))), ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </div>
                                <div class="text-end small">
                                    <div><?= htmlspecialchars((string) $actor['total'], ENT_QUOTES, 'UTF-8') ?> Events</div>
                                    <div class="text-danger"><?= htmlspecialchars((string) $actor['failure'], ENT_QUOTES, 'UTF-8') ?> Fehler</div>
                                </div>
                            </div>
                            <div class="progress" role="progressbar" aria-valuenow="<?= htmlspecialchars((string) $actor['total'], ENT_QUOTES, 'UTF-8') ?>" aria-valuemin="0" aria-valuemax="<?= htmlspecialchars((string) $actorMax, ENT_QUOTES, 'UTF-8') ?>">
                                <div class="progress-bar bg-primary" style="width: <?= htmlspecialchars((string) $actorWidth, ENT_QUOTES, 'UTF-8') ?>%"></div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card card-soft h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="eyebrow mb-1">Fehlerbild</p>
                    <h2 class="h4 mb-0">Failure Heatmap nach Quelle</h2>
                </div>
                <span class="dashboard-role-badge">Rate</span>
            </div>

            <?php if (($failureHeatmap ?? []) === []): ?>
                <p class="muted mb-0">Keine Fehlerdaten fuer den aktuellen Filter.</p>
            <?php else: ?>
                <div class="d-grid gap-3">
                    <?php foreach ($failureHeatmap as $sourceKey => $source): ?>
                        <?php
                        $heatClass = 'bg-success';

                        if ((int) $source['failure_rate'] >= 50) {
                            $heatClass = 'bg-danger';
                        } elseif ((int) $source['failure_rate'] >= 20) {
                            $heatClass = 'bg-warning';
                        }
                        ?>
                        <article class="border rounded-4 p-3 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong><a class="text-decoration-none text-reset" href="/audit?source=<?= urlencode((string) $sourceKey) ?>&outcome=failure"><?= htmlspecialchars((string) $source['label'], ENT_QUOTES, 'UTF-8') ?></a></strong>
                                <span><?= htmlspecialchars((string) $source['failure_rate'], ENT_QUOTES, 'UTF-8') ?>%</span>
                            </div>
                            <div class="progress mb-2" role="progressbar" aria-valuenow="<?= htmlspecialchars((string) $source['failure_rate'], ENT_QUOTES, 'UTF-8') ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar <?= htmlspecialchars((string) $heatClass, ENT_QUOTES, 'UTF-8') ?>" style="width: <?= htmlspecialchars((string) $source['failure_rate'], ENT_QUOTES, 'UTF-8') ?>%"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-secondary">
                                <span>Total: <?= htmlspecialchars((string) $source['total'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span>Fehler: <?= htmlspecialchars((string) $source['failure'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
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

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-5">
        <div class="card card-soft h-100">
            <p class="eyebrow">Presets</p>
            <h2 class="h4 mb-2">Aktuelle Filter speichern</h2>
            <p class="muted mb-3">Speichert die aktuelle Kombination aus Quelle, Suche, Outcome und Datumsbereich als wiederverwendbares Audit-Preset.</p>
            <form method="POST" action="/audit/presets">
                <input type="hidden" name="_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="return_to" value="<?= htmlspecialchars((string) ($currentAuditUrl ?? '/audit'), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="source" value="<?= htmlspecialchars((string) ($filters['source'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="search" value="<?= htmlspecialchars((string) ($filters['search'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="outcome" value="<?= htmlspecialchars((string) ($filters['outcome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="date_from" value="<?= htmlspecialchars((string) ($filters['date_from'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="date_to" value="<?= htmlspecialchars((string) ($filters['date_to'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="preset_name">Preset-Name</label>
                    <input class="form-control" id="preset_name" name="name" maxlength="120" placeholder="z. B. Mail Fehler letzte 7 Tage" required>
                </div>

                <?php if (empty($savePresetAllowed)): ?>
                    <p class="muted mb-3">Mindestens ein Filter muss gesetzt sein, bevor ein Preset gespeichert werden kann.</p>
                <?php else: ?>
                    <p class="muted mb-3">Wenn bereits ein Preset mit demselben Namen existiert, wird es mit den aktuellen Filtern aktualisiert.</p>
                <?php endif; ?>

                <button class="btn px-4 py-2" type="submit" <?= empty($savePresetAllowed) ? 'disabled' : '' ?>>Preset speichern</button>
            </form>
        </div>
    </div>
    <div class="col-12 col-xl-7">
        <div class="card card-soft h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="eyebrow mb-1">Presets</p>
                    <h2 class="h4 mb-0">Gespeicherte Filter</h2>
                </div>
                <span class="dashboard-role-badge"><?= htmlspecialchars((string) count($savedPresets ?? []), ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <?php if (($savedPresets ?? []) === []): ?>
                <p class="muted mb-0">Noch keine gespeicherten Audit-Presets vorhanden.</p>
            <?php else: ?>
                <div class="d-grid gap-3">
                    <?php foreach ($savedPresets as $preset): ?>
                        <article class="border rounded-4 p-3 bg-white">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div>
                                    <strong><?= htmlspecialchars((string) ($preset['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if (($preset['summary'] ?? []) !== []): ?>
                                        <div class="small text-secondary mt-1">
                                            <?= htmlspecialchars(implode(' | ', (array) $preset['summary']), ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-outline-accent btn-sm" href="<?= htmlspecialchars((string) ($preset['url'] ?? '/audit'), ENT_QUOTES, 'UTF-8') ?>">Anwenden</a>
                                    <form method="POST" action="/audit/presets/<?= htmlspecialchars((string) ($preset['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>/delete" class="m-0">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="return_to" value="<?= htmlspecialchars((string) ($currentAuditUrl ?? '/audit'), ENT_QUOTES, 'UTF-8') ?>">
                                        <button class="btn btn-outline-accent btn-sm" type="submit">Loeschen</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
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
                            <h3 class="h5 mb-1"><a class="text-decoration-none text-reset" href="<?= htmlspecialchars((string) ($event['dashboard_url'] ?? '/audit'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) ($event['subject'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></a></h3>
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
