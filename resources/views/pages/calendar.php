<?php
$title = 'Calendar';
$editingEvent = $editingEvent ?? null;
$formValues = [
    'title' => (string) ($old['title'] ?? ($editingEvent['title'] ?? '')),
    'description' => (string) ($old['description'] ?? ($editingEvent['description'] ?? '')),
    'location' => (string) ($old['location'] ?? ($editingEvent['location'] ?? '')),
    'starts_at' => (string) ($old['starts_at'] ?? (!empty($editingEvent['starts_at']) ? date('Y-m-d\TH:i', strtotime((string) $editingEvent['starts_at'])) : '')),
    'ends_at' => (string) ($old['ends_at'] ?? (!empty($editingEvent['ends_at']) ? date('Y-m-d\TH:i', strtotime((string) $editingEvent['ends_at'])) : '')),
    'department_ids' => (array) ($old['department_ids'] ?? ($editingEvent['department_ids'] ?? [])),
];
$isEditMode = $editingEvent !== null || (int) ($old['edit_id'] ?? 0) > 0;
$showCreateForm = !empty($error)
    || $isEditMode
    || !empty($old['title'] ?? '')
    || !empty($old['description'] ?? '')
    || !empty($old['location'] ?? '')
    || !empty($old['starts_at'] ?? '')
    || !empty($old['ends_at'] ?? '')
    || !empty($old['department_ids'] ?? []);
$editTargetId = (int) ($editingEvent['id'] ?? ($old['edit_id'] ?? 0));
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
            <?= $isEditMode ? 'Termin aktualisieren' : 'Neuer Termin' ?>
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
            <p class="eyebrow"><?= $isEditMode ? 'Termin bearbeiten' : 'Neuer Termin' ?></p>
            <h2 class="h4 mb-4"><?= $isEditMode ? 'Kalendereintrag aktualisieren' : 'Kalendereintrag erstellen' ?></h2>
            <form method="POST" action="<?= $isEditMode ? '/calendar/events/' . (int) ($editingEvent['id'] ?? $old['edit_id'] ?? 0) . '/update' : '/calendar/events' ?>" class="d-flex flex-column gap-3">
                <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div>
                    <label class="form-label fw-semibold" for="title">Titel</label>
                    <input class="form-control" id="title" name="title" required value="<?= htmlspecialchars($formValues['title'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div>
                    <label class="form-label fw-semibold" for="description">Beschreibung</label>
                    <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($formValues['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div>
                    <label class="form-label fw-semibold" for="location">Ort</label>
                    <input class="form-control" id="location" name="location" value="<?= htmlspecialchars($formValues['location'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div>
                    <label class="form-label fw-semibold" for="starts_at">Beginn</label>
                    <input class="form-control" id="starts_at" type="datetime-local" name="starts_at" required value="<?= htmlspecialchars($formValues['starts_at'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div>
                    <label class="form-label fw-semibold" for="ends_at">Ende</label>
                    <input class="form-control" id="ends_at" type="datetime-local" name="ends_at" value="<?= htmlspecialchars($formValues['ends_at'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div>
                    <label class="form-label fw-semibold" for="department_ids">Abteilungen markieren</label>
                    <select class="form-select" id="department_ids" name="department_ids[]" multiple size="6">
                        <?php foreach ($departments as $department): ?>
                            <?php $selected = in_array((string) $department['id'], array_map('strval', $formValues['department_ids'] ?? []), true); ?>
                            <option value="<?= htmlspecialchars((string) $department['id'], ENT_QUOTES, 'UTF-8') ?>" <?= $selected ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $department['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Markierte Abteilungen erhalten eine interne Mail-Benachrichtigung.</div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn px-4 py-2 align-self-start" type="submit"><?= $isEditMode ? 'Termin aktualisieren' : 'Termin speichern' ?></button>
                    <?php if ($isEditMode): ?>
                        <a class="btn btn-outline-accent px-4 py-2" href="/calendar">Abbrechen</a>
                    <?php endif; ?>
                </div>
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
                        <article
                            class="border rounded-4 p-4 bg-white<?= $isOverdue ? ' calendar-overdue' : '' ?> calendar-event-card"
                            data-event-id="<?= htmlspecialchars((string) $event['id'], ENT_QUOTES, 'UTF-8') ?>"
                            data-event-title="<?= htmlspecialchars((string) $event['title'], ENT_QUOTES, 'UTF-8') ?>"
                            data-event-starts-at="<?= $startsAt instanceof DateTimeInterface ? htmlspecialchars($startsAt->format(DateTimeInterface::ATOM), ENT_QUOTES, 'UTF-8') : '' ?>"
                        >
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
                                        <?php if ((int) ($event['created_by'] ?? 0) === (int) ($user['id'] ?? 0) || (($user['role_name'] ?? null) === 'admin')): ?>
                                            <a class="btn btn-outline-accent btn-sm px-3 py-2" href="/calendar?edit=<?= htmlspecialchars((string) $event['id'], ENT_QUOTES, 'UTF-8') ?>#calendarCreateForm">Edit</a>
                                            <form method="POST" action="/calendar/events/<?= htmlspecialchars((string) $event['id'], ENT_QUOTES, 'UTF-8') ?>/delete" onsubmit="return window.confirm('Termin wirklich loeschen?');">
                                                <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                <button class="btn btn-outline-danger btn-sm px-3 py-2" type="submit">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" action="/calendar/events/<?= htmlspecialchars((string) $event['id'], ENT_QUOTES, 'UTF-8') ?>/complete">
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editTargetId = <?= $editTargetId ?>;
        const createForm = document.getElementById('calendarCreateForm');

        if (editTargetId > 0 && createForm) {
            createForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        const eventCards = Array.from(document.querySelectorAll('.calendar-event-card'));

        if (eventCards.length === 0) {
            return;
        }

        const reminderWindowInSeconds = 3600;
        const reminderStorageKey = 'calendar_event_reminders_v1';
        const loadReminderState = function () {
            try {
                return JSON.parse(window.localStorage.getItem(reminderStorageKey) || '{}');
            } catch (error) {
                return {};
            }
        };
        const saveReminderState = function (state) {
            window.localStorage.setItem(reminderStorageKey, JSON.stringify(state));
        };
        const reminderState = loadReminderState();
        const supportsNotifications = 'Notification' in window;

        const showReminder = function (eventTitle) {
            const message = 'Termin-Erinnerung: "' + eventTitle + '" beginnt in weniger als 1 Stunde.';

            if (!supportsNotifications) {
                window.alert(message);
                return;
            }

            if (window.Notification.permission === 'granted') {
                const notification = new window.Notification('Termin-Erinnerung', {
                    body: '"' + eventTitle + '" beginnt in weniger als 1 Stunde.',
                    tag: 'calendar-reminder-' + eventTitle,
                });

                window.setTimeout(function () {
                    notification.close();
                }, 10000);

                return;
            }

            window.alert(message);
        };

        if (supportsNotifications && window.Notification.permission === 'default') {
            window.Notification.requestPermission().catch(function () {
                return 'default';
            });
        }

        const checkEventReminders = function () {
            const now = Date.now();
            let didChange = false;

            eventCards.forEach(function (card) {
                const eventId = card.dataset.eventId || '';
                const eventTitle = card.dataset.eventTitle || 'Termin';
                const startsAt = card.dataset.eventStartsAt || '';

                if (!eventId || !startsAt) {
                    return;
                }

                const startTimestamp = Date.parse(startsAt);

                if (Number.isNaN(startTimestamp)) {
                    return;
                }

                const secondsUntilStart = Math.floor((startTimestamp - now) / 1000);
                const reminderToken = eventId + '|' + startsAt;

                if (secondsUntilStart > reminderWindowInSeconds || secondsUntilStart < 0) {
                    return;
                }

                if (reminderState[reminderToken]) {
                    return;
                }

                showReminder(eventTitle);
                reminderState[reminderToken] = true;
                didChange = true;
            });

            if (didChange) {
                saveReminderState(reminderState);
            }
        };

        checkEventReminders();
        window.setInterval(checkEventReminders, 60000);
    });
</script>
