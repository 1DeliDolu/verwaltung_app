<?php
$title = 'Calendar';
$showCreateForm = !empty($error)
    || !empty($old['title'] ?? '')
    || !empty($old['description'] ?? '')
    || !empty($old['location'] ?? '')
    || !empty($old['starts_at'] ?? '')
    || !empty($old['ends_at'] ?? '')
    || !empty($old['department_ids'] ?? []);
?>
<style>
    .calendar-state-badge-orange {
        background: #f59e0b;
        color: #fff;
    }
    .calendar-overdue {
        border: 3px solid #b91c1c !important;
    }
    .calendar-overdue .calendar-title {
        color: #b91c1c;
        font-weight: 800;
    }
</style>
<div class="hero">
    <p class="eyebrow">Gemeinsamer Kalender</p>
    <h1 class="display-6 fw-semibold">Termine und Bereichsplanung</h1>
    <p class="lead">Termine werden zentral gespeichert, nach dem naechsten Datum sortiert angezeigt und koennen beim Erstellen direkt an markierte Abteilungen gemeldet werden.</p>
</div>

<?php if ($user !== null): ?>
    <div class="d-flex justify-content-end mb-4">
        <button
            class="btn px-4 py-2"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#calendarCreateForm"
            aria-expanded="<?= $showCreateForm ? 'true' : 'false' ?>"
            aria-controls="calendarCreateForm"
        >
            Neuer Termin
        </button>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($user !== null): ?>
    <div class="collapse mb-4<?= $showCreateForm ? ' show' : '' ?>" id="calendarCreateForm">
        <div class="card card-soft">
            <p class="eyebrow">Neuer Termin</p>
            <h2 class="h4 mb-4">Kalendereintrag erstellen</h2>
            <form method="POST" action="/calendar/events" class="d-flex flex-column gap-3">
                <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div>
                    <label class="form-label fw-semibold" for="title">Titel</label>
                    <input class="form-control" id="title" name="title" required value="<?= htmlspecialchars((string) ($old['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div>
                    <label class="form-label fw-semibold" for="description">Beschreibung</label>
                    <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars((string) ($old['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div>
                    <label class="form-label fw-semibold" for="location">Ort</label>
                    <input class="form-control" id="location" name="location" value="<?= htmlspecialchars((string) ($old['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div>
                    <label class="form-label fw-semibold" for="starts_at">Beginn</label>
                    <input class="form-control" id="starts_at" type="datetime-local" name="starts_at" required value="<?= htmlspecialchars((string) ($old['starts_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div>
                    <label class="form-label fw-semibold" for="ends_at">Ende</label>
                    <input class="form-control" id="ends_at" type="datetime-local" name="ends_at" value="<?= htmlspecialchars((string) ($old['ends_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div>
                    <label class="form-label fw-semibold" for="department_ids">Abteilungen markieren</label>
                    <select class="form-select" id="department_ids" name="department_ids[]" multiple size="6">
                        <?php foreach ($departments as $department): ?>
                            <?php $selected = in_array((string) $department['id'], array_map('strval', $old['department_ids'] ?? []), true); ?>
                            <option value="<?= htmlspecialchars((string) $department['id'], ENT_QUOTES, 'UTF-8') ?>" <?= $selected ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $department['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Markierte Abteilungen erhalten eine interne Mail-Benachrichtigung.</div>
                </div>

                <button class="btn px-4 py-2 align-self-start" type="submit">Termin speichern</button>
            </form>
        </div>
    </div>
<?php elseif ($user === null): ?>
    <div class="alert alert-light border mb-4">Bitte melde dich an, um Termine anzulegen und Abteilungen per Mail zu benachrichtigen.</div>
<?php endif; ?>

<div class="row g-4 align-items-start">
    <div class="col-12">
        <div class="card card-soft h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="eyebrow mb-1">Terminliste</p>
                    <h2 class="h4 mb-0">Von nah bis fern sortiert</h2>
                </div>
                <span class="badge text-bg-light rounded-pill"><?= count($events) ?> Termine</span>
            </div>

            <?php if ($events === []): ?>
                <p class="mb-0">Noch keine Termine im Kalender.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($events as $event): ?>
                        <?php
                        $startsAt = date_create((string) $event['starts_at']);
                        $endsAt = !empty($event['ends_at']) ? date_create((string) $event['ends_at']) : null;
                        $departmentNames = array_filter(array_map('trim', explode(',', (string) ($event['department_names'] ?? ''))));
                        $statusLabel = 'Geplant';
                        $statusClass = 'text-bg-secondary';
                        $isOverdue = false;

                        if ($startsAt instanceof DateTimeInterface) {
                            $now = new DateTimeImmutable();
                            $eventAt = DateTimeImmutable::createFromMutable($startsAt instanceof DateTime ? $startsAt : new DateTime($startsAt->format('Y-m-d H:i:s')));
                            $secondsUntil = $eventAt->getTimestamp() - $now->getTimestamp();
                            $daysUntil = (int) floor($secondsUntil / 86400);

                            if ($secondsUntil < 0) {
                                $statusLabel = 'Nicht erledigt';
                                $statusClass = 'text-bg-danger';
                                $isOverdue = true;
                            } elseif ($daysUntil <= 1) {
                                $statusLabel = '1 Tag';
                                $statusClass = 'text-bg-danger';
                            } elseif ($daysUntil <= 2) {
                                $statusLabel = '2 Tage';
                                $statusClass = 'calendar-state-badge-orange';
                            } elseif ($daysUntil <= 3) {
                                $statusLabel = '3 Tage';
                                $statusClass = 'text-bg-warning';
                            }
                        }
                        ?>
                        <article class="border rounded-4 p-4 bg-white<?= $isOverdue ? ' calendar-overdue' : '' ?>">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <p class="eyebrow mb-0"><?= $startsAt instanceof DateTimeInterface ? htmlspecialchars($startsAt->format('d.m.Y H:i'), ENT_QUOTES, 'UTF-8') : '' ?></p>
                                        <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <h3 class="h5 mb-2 calendar-title"><?= htmlspecialchars((string) $event['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p class="mb-3"><?= nl2br(htmlspecialchars((string) $event['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                                    <div class="small text-secondary">
                                        Erstellt von <?= htmlspecialchars((string) $event['created_by_name'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php if (!empty($event['location'])): ?>
                                            | Ort: <?= htmlspecialchars((string) $event['location'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                        <?php if ($endsAt instanceof DateTimeInterface): ?>
                                            | Ende: <?= htmlspecialchars($endsAt->format('d.m.Y H:i'), ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-content-start">
                                    <?php if ($departmentNames === []): ?>
                                        <span class="badge text-bg-secondary">Ohne Abteilung</span>
                                    <?php else: ?>
                                        <?php foreach ($departmentNames as $departmentName): ?>
                                            <span class="badge text-bg-primary"><?= htmlspecialchars($departmentName, ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <?php if ($user !== null): ?>
                                        <form method="POST" action="/calendar/events/<?= htmlspecialchars((string) $event['id'], ENT_QUOTES, 'UTF-8') ?>/complete" class="ms-lg-2">
                                            <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <button class="btn btn-sm px-3 py-2" type="submit">Erledigt</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
