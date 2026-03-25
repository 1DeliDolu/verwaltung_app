<?php $title = 'Task'; ?>
<div class="hero">
    <p class="eyebrow"><?= htmlspecialchars((string) ($task['department_name'] ?? 'Task'), ENT_QUOTES, 'UTF-8') ?></p>
    <h1 class="display-6 fw-semibold"><?= htmlspecialchars((string) $task['title'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="lead">Detailansicht fuer Status, Verantwortung und Teamkommunikation.</p>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-8">
        <div class="card card-soft h-100">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                <div>
                    <p class="eyebrow mb-2">Beschreibung</p>
                    <p class="mb-0" style="white-space: pre-line;"><?= htmlspecialchars((string) $task['description'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="dashboard-role-badge"><?= htmlspecialchars((string) ($statuses[$task['status']] ?? $task['status']), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-6"><strong>Prioritaet:</strong> <?= htmlspecialchars((string) ($priorities[$task['priority']] ?? $task['priority']), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="col-12 col-md-6"><strong>Faelligkeit:</strong> <?= htmlspecialchars((string) ($task['due_date'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="col-12 col-md-6"><strong>Erstellt von:</strong> <?= htmlspecialchars((string) ($task['creator_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="col-12 col-md-6"><strong>Zugewiesen an:</strong> <?= htmlspecialchars((string) ($task['assignee_name'] ?? 'Nicht zugewiesen'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-4">
                <?php if ($canManage): ?>
                    <a class="btn btn-outline-accent px-4 py-2" href="/tasks/<?= htmlspecialchars((string) $task['id'], ENT_QUOTES, 'UTF-8') ?>/edit">Bearbeiten</a>
                <?php endif; ?>
                <a class="btn btn-outline-accent px-4 py-2" href="/tasks">Zur Liste</a>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card card-soft h-100">
            <p class="eyebrow">Workflow</p>
            <h2 class="h4 mb-3">Status aendern</h2>
            <?php if ($canWork): ?>
                <form method="POST" action="/tasks/<?= htmlspecialchars((string) $task['id'], ENT_QUOTES, 'UTF-8') ?>/status">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="status">Neuer Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <?php foreach ($statuses as $statusKey => $statusLabel): ?>
                                <option value="<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8') ?>" <?= (string) $task['status'] === $statusKey ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn px-4 py-2" type="submit">Status speichern</button>
                </form>
            <?php else: ?>
                <p class="muted mb-0">Nur Ersteller, Zustaendige, Teamleiter oder Admins duerfen den Status aendern.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card card-soft">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Kommentare</p>
            <h2 class="h4 mb-0">Teamkommunikation</h2>
        </div>
        <div class="dashboard-role-badge"><?= count($comments) ?> Eintraege</div>
    </div>

    <form method="POST" action="/tasks/<?= htmlspecialchars((string) $task['id'], ENT_QUOTES, 'UTF-8') ?>/comments" class="mb-4">
        <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <label class="form-label fw-semibold" for="comment_body">Kommentar</label>
        <textarea class="form-control" id="comment_body" name="body" rows="4" required></textarea>
        <button class="btn px-4 py-2 mt-3" type="submit">Kommentar speichern</button>
    </form>

    <?php if ($comments === []): ?>
        <p class="muted mb-0">Noch keine Kommentare vorhanden.</p>
    <?php else: ?>
        <div class="d-grid gap-3">
            <?php foreach ($comments as $comment): ?>
                <article class="border rounded-4 p-3 bg-white">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-2 mb-2">
                        <strong><?= htmlspecialchars((string) ($comment['author_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
                        <span class="muted"><?= htmlspecialchars((string) ($comment['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <p class="mb-0" style="white-space: pre-line;"><?= htmlspecialchars((string) $comment['body'], ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
