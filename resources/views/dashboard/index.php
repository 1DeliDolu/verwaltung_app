<?php $title = 'Dashboard'; ?>
<div class="topbar d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
    <div>
        <h1 class="display-6 fw-semibold mb-2">Department Operations Portal</h1>
        <p class="muted mb-0">Uebersicht fuer angemeldete Benutzer.</p>
    </div>
    <form method="POST" action="/logout">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($app->session()->get('_csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
        <button class="btn px-4 py-2" type="submit">Abmelden</button>
    </form>
</div>

<div class="card card-soft">
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <div class="row g-3">
        <div class="col-12 col-md-4"><strong>Willkommen, <?= htmlspecialchars((string) ($user['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></div>
        <div class="col-12 col-md-4">E-Mail: <?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="col-12 col-md-4">Rolle: <?= htmlspecialchars((string) ($user['role_name'] ?? 'employee'), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <hr>
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <p class="eyebrow mb-1">MailHog Probe</p>
            <p class="mb-0 muted">Sende eine Testmail an <?= htmlspecialchars((string) $app->config('mail.test_recipient', 'admin@verwaltung.demo'), ENT_QUOTES, 'UTF-8') ?> und pruefe sie im MailHog UI.</p>
        </div>
        <form method="POST" action="/mail/demo-send" class="m-0">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($app->session()->get('_csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
            <button class="btn px-4 py-2" type="submit">Testmail senden</button>
        </form>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12 col-lg-6">
        <a class="surface-link" href="/services">
            <div class="card card-soft h-100">
                <p class="eyebrow">Infrastruktur</p>
                <h2 class="h4">Mail- und Dateiserver</h2>
                <p class="mb-0">Geplante und aktive interne Dienste mit Verantwortlichkeiten.</p>
            </div>
        </a>
    </div>
    <div class="col-12 col-lg-6">
        <a class="surface-link" href="/departments">
            <div class="card card-soft h-100">
                <p class="eyebrow">Abteilungen</p>
                <h2 class="h4">Arbeitsbereiche</h2>
                <p class="mb-0">Alle zugaenglichen Abteilungen mit Dokumenten, Uploads und Fachaktionen.</p>
            </div>
        </a>
    </div>
    <div class="col-12">
        <section class="card card-soft">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <p class="eyebrow mb-1">Tasks</p>
                    <h2 class="h4 mb-2">Aufgabenlage</h2>
                    <p class="muted mb-0">Offene, laufende und blockierte Aufgaben bleiben direkt auf dem Dashboard sichtbar.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-accent px-4 py-2" href="/tasks">Alle Tasks</a>
                    <a class="btn px-4 py-2" href="/tasks/create">Neue Aufgabe</a>
                </div>
            </div>

            <div class="dashboard-stat-grid mb-4">
                <?php foreach ($taskStatuses as $statusKey => $statusLabel): ?>
                    <a class="dashboard-stat-tile text-decoration-none" href="/tasks?status=<?= urlencode($statusKey) ?>">
                        <span class="dashboard-stat-value"><?= htmlspecialchars((string) ($taskStatusCounts[$statusKey] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="dashboard-stat-label"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="row g-3">
                <?php if ($recentTasks === []): ?>
                    <div class="col-12">
                        <div class="border rounded-4 p-4 bg-white">
                            <p class="muted mb-0">Noch keine sichtbaren Aufgaben vorhanden.</p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php foreach ($recentTasks as $task): ?>
                    <div class="col-12 col-xl-6">
                        <a class="surface-link" href="/tasks/<?= htmlspecialchars((string) $task['id'], ENT_QUOTES, 'UTF-8') ?>">
                            <div class="border rounded-4 p-4 h-100 bg-white">
                                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                                    <div>
                                        <p class="eyebrow"><?= htmlspecialchars((string) ($task['department_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                        <h3 class="h5 mb-2"><?= htmlspecialchars((string) $task['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                        <p class="muted mb-0"><?= htmlspecialchars(mb_strimwidth((string) $task['description'], 0, 140, '...'), ENT_QUOTES, 'UTF-8') ?></p>
                                    </div>
                                    <div class="dashboard-role-badge"><?= htmlspecialchars((string) ($taskStatuses[$task['status']] ?? $task['status']), ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                                <div class="row g-2 small">
                                    <div class="col-12 col-md-6"><strong>Prioritaet:</strong> <?= htmlspecialchars((string) ($taskPriorities[$task['priority']] ?? $task['priority']), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="col-12 col-md-6"><strong>Faellig:</strong> <?= htmlspecialchars((string) ($task['due_date'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>

<div class="hero">
    <p class="eyebrow">Schnellzugriff</p>
    <h2 class="h3 fw-semibold mb-2">Abteilungsaktionen direkt vom Dashboard</h2>
    <p class="lead mb-0">Jede Abteilung nutzt dieselbe Kartenlogik. Standardaktionen und berechtigte Fachaktionen sind ohne Umweg direkt von hier erreichbar.</p>
</div>

<div class="row g-4">
    <?php foreach ($departments as $department): ?>
        <div class="col-12 col-xl-6">
            <section class="card card-soft h-100">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                    <div>
                        <p class="eyebrow"><?= htmlspecialchars((string) ($department['membership_role'] ?? $user['role_name'] ?? 'member'), ENT_QUOTES, 'UTF-8') ?></p>
                        <h2 class="h4 mb-2"><?= htmlspecialchars((string) $department['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="muted mb-0"><?= htmlspecialchars((string) ($department['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="dashboard-role-badge">
                        <?= htmlspecialchars((string) (($department['can_manage'] ?? false) ? 'Leitung / Verwaltung' : 'Lesender Zugriff'), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>

                <div class="dashboard-action-grid">
                    <?php foreach (($department['quick_actions'] ?? []) as $action): ?>
                        <a
                            class="btn px-4 py-2<?= ($action['variant'] ?? 'secondary') === 'secondary' ? ' btn-outline-accent' : '' ?>"
                            href="<?= htmlspecialchars((string) $action['href'], ENT_QUOTES, 'UTF-8') ?>"
                        >
                            <?= htmlspecialchars((string) $action['label'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="dashboard-stat-grid">
                    <?php foreach (($department['summary_stats'] ?? []) as $stat): ?>
                        <div class="dashboard-stat-tile">
                            <span class="dashboard-stat-value"><?= htmlspecialchars((string) $stat['value'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="dashboard-stat-label"><?= htmlspecialchars((string) $stat['label'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    <?php endforeach; ?>
</div>
