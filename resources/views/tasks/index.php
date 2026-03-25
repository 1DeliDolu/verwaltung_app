<?php $title = 'Tasks'; ?>
<div class="hero">
    <p class="eyebrow">Aufgaben</p>
    <h1 class="display-6 fw-semibold">Teamweite Aufgabensteuerung</h1>
    <p class="lead">Aufgaben bleiben pro Abteilung sichtbar, mit klarer Prioritaet, Status und Verantwortlichen.</p>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="card card-soft mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <p class="eyebrow mb-1">Uebersicht</p>
            <h2 class="h4 mb-2">Status und Fokus</h2>
            <p class="muted mb-0">Filtere Aufgaben nach Status oder springe direkt in neue Vorgaben fuer dein Team.</p>
        </div>
        <a class="btn px-4 py-2" href="/tasks/create<?= $activeDepartmentId > 0 ? '?department_id=' . urlencode((string) $activeDepartmentId) : '' ?>">Neue Aufgabe</a>
    </div>
    <div class="dashboard-stat-grid mt-4">
        <?php foreach ($statuses as $statusKey => $statusLabel): ?>
            <?php $statusHref = '/tasks?status=' . urlencode($statusKey) . ($activeDepartmentId > 0 ? '&department_id=' . urlencode((string) $activeDepartmentId) : ''); ?>
            <a class="dashboard-stat-tile text-decoration-none" href="<?= $activeStatus === $statusKey ? '/tasks' . ($activeDepartmentId > 0 ? '?department_id=' . urlencode((string) $activeDepartmentId) : '') : $statusHref ?>">
                <span class="dashboard-stat-value"><?= htmlspecialchars((string) ($statusCounts[$statusKey] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="dashboard-stat-label"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="card card-soft mb-4">
    <form method="GET" action="/tasks" class="row g-3 align-items-end">
        <div class="col-12 col-lg-6">
            <label class="form-label fw-semibold" for="department_id">Abteilung</label>
            <select class="form-select" id="department_id" name="department_id">
                <option value="">Alle sichtbaren Abteilungen</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= htmlspecialchars((string) $department['id'], ENT_QUOTES, 'UTF-8') ?>" <?= $activeDepartmentId === (int) $department['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $department['name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-lg-6">
            <label class="form-label fw-semibold" for="status">Status</label>
            <select class="form-select" id="status" name="status">
                <option value="">Alle Stati</option>
                <?php foreach ($statuses as $statusKey => $statusLabel): ?>
                    <option value="<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8') ?>" <?= $activeStatus === $statusKey ? 'selected' : '' ?>>
                        <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 d-flex flex-wrap gap-2">
            <button class="btn px-4 py-2" type="submit">Filter anwenden</button>
            <a class="btn btn-outline-accent px-4 py-2" href="/tasks">Filter zuruecksetzen</a>
        </div>
    </form>
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
    <a class="btn px-4 py-2<?= $activeStatus === '' ? '' : ' btn-outline-accent' ?>" href="/tasks<?= $activeDepartmentId > 0 ? '?department_id=' . urlencode((string) $activeDepartmentId) : '' ?>">Alle</a>
    <?php foreach ($statuses as $statusKey => $statusLabel): ?>
        <a class="btn px-4 py-2<?= $activeStatus === $statusKey ? '' : ' btn-outline-accent' ?>" href="/tasks?status=<?= urlencode($statusKey) ?><?= $activeDepartmentId > 0 ? '&department_id=' . urlencode((string) $activeDepartmentId) : '' ?>">
            <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <?php if ($tasks === []): ?>
        <div class="col-12">
            <div class="card card-soft">
                <p class="mb-0 muted">Keine Aufgaben fuer den aktuellen Filter gefunden.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($tasks as $task): ?>
        <div class="col-12 col-xl-6">
            <a class="surface-link" href="/tasks/<?= htmlspecialchars((string) $task['id'], ENT_QUOTES, 'UTF-8') ?>">
                <article class="card card-soft h-100">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                        <div>
                            <p class="eyebrow"><?= htmlspecialchars((string) ($task['department_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                            <h2 class="h4 mb-2"><?= htmlspecialchars((string) $task['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <p class="muted mb-0"><?= htmlspecialchars(mb_strimwidth((string) $task['description'], 0, 180, '...'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="dashboard-role-badge"><?= htmlspecialchars((string) ($statuses[$task['status']] ?? $task['status']), ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <strong>Prioritaet:</strong> <?= htmlspecialchars((string) ($priorities[$task['priority']] ?? $task['priority']), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <strong>Faellig:</strong> <?= htmlspecialchars((string) ($task['due_date'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <strong>Erstellt von:</strong> <?= htmlspecialchars((string) ($task['creator_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <strong>Zugewiesen:</strong> <?= htmlspecialchars((string) ($task['assignee_name'] ?? 'Nicht zugewiesen'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </div>
                </article>
            </a>
        </div>
    <?php endforeach; ?>
</div>
