<?php $title = $department['name'] . ' Dokumente'; ?>
<div class="hero">
    <p class="eyebrow">Abteilung</p>
    <h1 class="display-6 fw-semibold"><?= htmlspecialchars((string) $department['name'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="lead">
        <?= htmlspecialchars((string) ($department['hero_text'] ?: ($department['description'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
    </p>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-4">
        <div class="card card-soft h-100">
            <p class="eyebrow">Profil</p>
            <h2 class="h4 mb-3">Bereichsfokus</h2>
            <?php if (!empty($department['tagline'])): ?>
                <p class="mb-3"><?= htmlspecialchars((string) $department['tagline'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if (!empty($department['focus'])): ?>
                <p class="mb-0"><strong>Fokus:</strong> <?= htmlspecialchars((string) $department['focus'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card card-soft h-100">
            <p class="eyebrow">Verantwortung</p>
            <h2 class="h4 mb-3">Kernaufgaben</h2>
            <?php if (($department['responsibilities'] ?? []) === []): ?>
                <p class="muted mb-0">Keine zusaetzlichen Kernaufgaben konfiguriert.</p>
            <?php else: ?>
                <ul class="mb-0 ps-3">
                    <?php foreach ($department['responsibilities'] as $responsibility): ?>
                        <li><?= htmlspecialchars((string) $responsibility, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card card-soft h-100">
            <p class="eyebrow">Ablauf</p>
            <h2 class="h4 mb-3">Typische Workflows</h2>
            <?php if (($department['workflows'] ?? []) === []): ?>
                <p class="muted mb-0">Keine zusaetzlichen Workflows konfiguriert.</p>
            <?php else: ?>
                <ul class="mb-0 ps-3">
                    <?php foreach ($department['workflows'] as $workflow): ?>
                        <li><?= htmlspecialchars((string) $workflow, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($canManage && ($department['leader_tasks'] ?? []) !== []): ?>
    <div class="card card-soft mb-4" id="department-leader-workspace">
        <p class="eyebrow">Leitung</p>
        <h2 class="h4 mb-3"><?= htmlspecialchars((string) ($department['leader_title'] ?? 'Leiterarbeitsplatz'), ENT_QUOTES, 'UTF-8') ?></h2>
        <?php if (!empty($department['leader_intro'])): ?>
            <p class="muted mb-4"><?= htmlspecialchars((string) $department['leader_intro'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <div class="row g-4">
            <?php foreach ($department['leader_tasks'] as $leaderTask): ?>
                <div class="col-12 col-lg-6">
                    <div class="border rounded-4 p-4 h-100 bg-white">
                        <h3 class="h5 mb-2"><?= htmlspecialchars((string) $leaderTask['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="muted mb-3"><?= htmlspecialchars((string) $leaderTask['description'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php if (!empty($leaderTask['action_label']) && !empty($leaderTask['action_target'])): ?>
                            <a class="btn btn-outline-accent px-4 py-2" href="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?><?= htmlspecialchars((string) $leaderTask['action_target'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars((string) $leaderTask['action_label'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card card-soft mb-4" id="department-tasks">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Tasks</p>
            <h2 class="h4 mb-2">Aufgaben in <?= htmlspecialchars((string) $department['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="muted mb-0">Alle Aufgaben bleiben in derselben Task-Logik, hier nur auf diese Abteilung gefiltert.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-accent px-4 py-2" href="/tasks?department_id=<?= htmlspecialchars((string) $department['id'], ENT_QUOTES, 'UTF-8') ?>">Alle ansehen</a>
            <?php if ($canManage): ?>
                <a class="btn px-4 py-2" href="/tasks/create?department_id=<?= htmlspecialchars((string) $department['id'], ENT_QUOTES, 'UTF-8') ?>">Task erstellen</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-stat-grid mb-4">
        <?php foreach ($taskStatuses as $statusKey => $statusLabel): ?>
            <a class="dashboard-stat-tile text-decoration-none" href="/tasks?department_id=<?= urlencode((string) $department['id']) ?>&status=<?= urlencode($statusKey) ?>">
                <span class="dashboard-stat-value"><?= htmlspecialchars((string) ($departmentTaskStatusCounts[$statusKey] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
                <span class="dashboard-stat-label"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="row g-3">
        <?php if ($departmentTasks === []): ?>
            <div class="col-12">
                <div class="border rounded-4 p-4 bg-white">
                    <p class="muted mb-0">Fuer diese Abteilung existieren noch keine sichtbaren Aufgaben.</p>
                </div>
            </div>
        <?php endif; ?>
        <?php foreach ($departmentTasks as $task): ?>
            <div class="col-12 col-xl-6">
                <a class="surface-link" href="/tasks/<?= htmlspecialchars((string) $task['id'], ENT_QUOTES, 'UTF-8') ?>">
                    <article class="border rounded-4 p-4 h-100 bg-white">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                            <div>
                                <h3 class="h5 mb-2"><?= htmlspecialchars((string) $task['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="muted mb-0"><?= htmlspecialchars(mb_strimwidth((string) $task['description'], 0, 160, '...'), ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div class="dashboard-role-badge"><?= htmlspecialchars((string) ($taskStatuses[$task['status']] ?? $task['status']), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <div class="row g-2 small">
                            <div class="col-12 col-md-6"><strong>Prioritaet:</strong> <?= htmlspecialchars((string) ($taskPriorities[$task['priority']] ?? $task['priority']), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="col-12 col-md-6"><strong>Faellig:</strong> <?= htmlspecialchars((string) ($task['due_date'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="col-12 col-md-6"><strong>Erstellt von:</strong> <?= htmlspecialchars((string) ($task['creator_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="col-12 col-md-6"><strong>Zugewiesen:</strong> <?= htmlspecialchars((string) ($task['assignee_name'] ?? 'Nicht zugewiesen'), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </article>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$departmentSpecificView = __DIR__ . '/' . (string) $department['slug'] . '/index.php';
if (is_file($departmentSpecificView) && filesize($departmentSpecificView) > 0) {
    require $departmentSpecificView;
}
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($canManage): ?>
    <?php
    $toggleGroups = [
        'department-document-create' => 'Neues Dokument anlegen',
        'department-file-upload' => 'Datei in Abteilungsordner hochladen',
        'department-managed-person-create' => 'Person technisch anlegen',
        'department-employee-create' => 'Personalprofil aus IT-Stammdaten anlegen',
        'department-employee-document-upload' => 'Personalakte hochladen',
    ];
    ?>
    <div class="card card-soft mb-4" id="department-document-create">
        <button class="department-form-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#department-document-create-panel" aria-expanded="false" aria-controls="department-document-create-panel">
            <span class="h4 mb-0"><?= htmlspecialchars((string) $toggleGroups['department-document-create'], ENT_QUOTES, 'UTF-8') ?></span>
            <span class="department-form-toggle-icon" aria-hidden="true">+</span>
        </button>
        <div class="collapse mt-4" id="department-document-create-panel">
            <form method="POST" action="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>/documents">
                <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="folder_name">Ordnername</label>
                    <input class="form-control" id="folder_name" name="folder_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="title">Dokumenttitel</label>
                    <input class="form-control" id="title" name="title" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold" for="body">Inhalt</label>
                    <textarea class="form-control" id="body" name="body" rows="6" required></textarea>
                </div>
                <button class="btn px-4 py-2" type="submit">Dokument speichern</button>
            </form>
        </div>
    </div>

    <div class="card card-soft mb-4" id="department-file-upload">
        <button class="department-form-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#department-file-upload-panel" aria-expanded="false" aria-controls="department-file-upload-panel">
            <span class="h4 mb-0"><?= htmlspecialchars((string) $toggleGroups['department-file-upload'], ENT_QUOTES, 'UTF-8') ?></span>
            <span class="department-form-toggle-icon" aria-hidden="true">+</span>
        </button>
        <div class="collapse mt-4" id="department-file-upload-panel">
            <form method="POST" action="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>/upload" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="mb-4">
                    <label class="form-label fw-semibold" for="upload_file">Datei</label>
                    <input class="form-control" id="upload_file" name="upload_file" type="file" required>
                </div>
                <button class="btn px-4 py-2" type="submit">Datei hochladen</button>
            </form>
        </div>
    </div>

    <?php if ($isInformationTechnologyDepartment): ?>
        <div class="card card-soft mb-4" id="department-managed-person-create">
            <button class="department-form-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#department-managed-person-create-panel" aria-expanded="false" aria-controls="department-managed-person-create-panel">
                <span class="h4 mb-0"><?= htmlspecialchars((string) $toggleGroups['department-managed-person-create'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="department-form-toggle-icon" aria-hidden="true">+</span>
            </button>
            <div class="collapse mt-4" id="department-managed-person-create-panel">
                <p class="muted">IT pflegt hier nur die minimal notwendigen Stammdaten fuer Konto, Zugriff und Erstanmeldung. HR ergaenzt anschliessend ausschliesslich im Personalbereich die sensiblen Personaldaten.</p>
                <form method="POST" action="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>/people">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="person_name">Name</label>
                            <input class="form-control" id="person_name" name="name" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="person_email">E-Mail</label>
                            <input class="form-control" id="person_email" name="email" type="email" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="target_department_id">Zielabteilung</label>
                            <select class="form-select" id="target_department_id" name="target_department_id" required>
                                <option value="">Bitte waehlen</option>
                                <?php foreach ($assignableDepartments as $assignableDepartment): ?>
                                    <option value="<?= htmlspecialchars((string) $assignableDepartment['id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars((string) $assignableDepartment['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="membership_role">Zugriffsrolle</label>
                            <select class="form-select" id="membership_role" name="membership_role" required>
                                <option value="employee">Employee</option>
                                <option value="team_leader">Team Leader</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="temporary_password">Temporaeres Passwort</label>
                            <input class="form-control" id="temporary_password" name="temporary_password" type="password" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="temporary_password_confirmation">Temporaeres Passwort bestaetigen</label>
                            <input class="form-control" id="temporary_password_confirmation" name="temporary_password_confirmation" type="password" required>
                        </div>
                    </div>
                    <p class="muted mt-3 mb-0">Passwortregel: mindestens 12 Zeichen, Gross-/Kleinbuchstaben, Zahl und Sonderzeichen. Beim ersten Login wird ein Passwortwechsel erzwungen.</p>
                    <button class="btn px-4 py-2 mt-4" type="submit">Person anlegen</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($isHumanResourcesDepartment): ?>
        <div class="card card-soft mb-4" id="department-employee-create">
            <button class="department-form-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#department-employee-create-panel" aria-expanded="false" aria-controls="department-employee-create-panel">
                <span class="h4 mb-0"><?= htmlspecialchars((string) $toggleGroups['department-employee-create'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="department-form-toggle-icon" aria-hidden="true">+</span>
            </button>
            <div class="collapse mt-4" id="department-employee-create-panel">
                <p class="muted">HR darf nur bereits durch IT angelegte Personen weiterverarbeiten. Die Personalnummer wird automatisch vergeben. Sensible Daten bleiben im HR-Bereich getrennt von den technischen Kontodaten.</p>
                <form method="POST" action="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>/employees">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="user_id">Von IT angelegte Person</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">Bitte waehlen</option>
                                <?php foreach ($eligiblePersonnelUsers as $eligibleUser): ?>
                                    <option value="<?= htmlspecialchars((string) $eligibleUser['id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars((string) $eligibleUser['name'], ENT_QUOTES, 'UTF-8') ?>
                                        | <?= htmlspecialchars((string) $eligibleUser['email'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php if (!empty($eligibleUser['department_name'])): ?>
                                            | <?= htmlspecialchars((string) $eligibleUser['department_name'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="position_title">Position</label>
                            <input class="form-control" id="position_title" name="position_title">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold" for="employment_status">Status</label>
                            <select class="form-select" id="employment_status" name="employment_status">
                                <option value="active">Aktiv</option>
                                <option value="on_leave">Beurlaubt</option>
                                <option value="inactive">Inaktiv</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold" for="hired_at">Eintrittsdatum</label>
                            <input class="form-control" id="hired_at" name="hired_at" type="date">
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold" for="data_processing_basis">Rechtsgrundlage der Verarbeitung</label>
                            <select class="form-select" id="data_processing_basis" name="data_processing_basis" required>
                                <option value="BDSG Paragraf 26 / DSGVO Art. 6 Abs. 1 lit. b">BDSG Paragraf 26 / DSGVO Art. 6 Abs. 1 lit. b</option>
                                <option value="DSGVO Art. 6 Abs. 1 lit. c">DSGVO Art. 6 Abs. 1 lit. c</option>
                                <option value="DSGVO Art. 6 Abs. 1 lit. f">DSGVO Art. 6 Abs. 1 lit. f</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold" for="retention_until">Aufbewahrung bis</label>
                            <input class="form-control" id="retention_until" name="retention_until" type="date">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="personnel_rights">Oezlukrechte und Leistungen</label>
                            <textarea class="form-control" id="personnel_rights" name="personnel_rights" rows="4"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="notes">Notizen</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"></textarea>
                        </div>
                    </div>
                    <button class="btn px-4 py-2 mt-4" type="submit">Mitarbeiter speichern</button>
                </form>
            </div>
        </div>

        <div class="card card-soft mb-4" id="department-employee-document-upload">
            <button class="department-form-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#department-employee-document-upload-panel" aria-expanded="false" aria-controls="department-employee-document-upload-panel">
                <span class="h4 mb-0"><?= htmlspecialchars((string) $toggleGroups['department-employee-document-upload'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="department-form-toggle-icon" aria-hidden="true">+</span>
            </button>
            <div class="collapse mt-4" id="department-employee-document-upload-panel">
                <form method="POST" action="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>/employees/documents" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="employee_id">Mitarbeiter</label>
                            <select class="form-select" id="employee_id" name="employee_id" required>
                                <option value="">Bitte waehlen</option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars((string) $employee['full_name'], ENT_QUOTES, 'UTF-8') ?>
                                        (<?= htmlspecialchars((string) $employee['employee_number'], ENT_QUOTES, 'UTF-8') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="employee_document">Dokument</label>
                            <input class="form-control" id="employee_document" name="employee_document" type="file" required>
                        </div>
                    </div>
                    <button class="btn px-4 py-2 mt-4" type="submit">Personalakte hochladen</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row g-4" id="department-documents">
    <?php foreach ($documents as $document): ?>
        <div class="col-12 col-lg-6">
            <article class="card card-soft h-100">
                <p class="eyebrow"><?= htmlspecialchars((string) $document['folder_name'], ENT_QUOTES, 'UTF-8') ?></p>
                <h2 class="h4"><?= htmlspecialchars((string) $document['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= nl2br(htmlspecialchars((string) $document['body'], ENT_QUOTES, 'UTF-8')) ?></p>
                <p class="muted mb-0">Erstellt von <?= htmlspecialchars((string) $document['created_by_name'], ENT_QUOTES, 'UTF-8') ?></p>
            </article>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($isHumanResourcesDepartment): ?>
    <div class="card card-soft mt-4">
        <p class="eyebrow">HR</p>
        <h2 class="h4 mb-4">Mitarbeiter und Personalakten</h2>
        <?php if ($employees === []): ?>
            <p class="muted mb-0">Noch keine Mitarbeiter im Personalbereich angelegt.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($employees as $employee): ?>
                    <div class="col-12">
                        <article class="card border-0 bg-transparent shadow-none p-0">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                                <div>
                                    <h3 class="h5 mb-1"><?= htmlspecialchars((string) ($employee['linked_user_name'] ?: $employee['full_name']), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p class="muted mb-1">
                                        Personalnummer <?= htmlspecialchars((string) $employee['employee_number'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php if (!empty($employee['position_title'])): ?>
                                            | <?= htmlspecialchars((string) $employee['position_title'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                    </p>
                                    <p class="muted mb-0">
                                        Status <?= htmlspecialchars((string) $employee['employment_status'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php if (!empty($employee['hired_at'])): ?>
                                            | Eintritt <?= htmlspecialchars((string) $employee['hired_at'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                        <?php if (!empty($employee['linked_department_name'])): ?>
                                            | Fachbereich <?= htmlspecialchars((string) $employee['linked_department_name'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <?php if (!empty($employee['linked_user_email']) || !empty($employee['email'])): ?>
                                    <div class="text-lg-end">
                                        <p class="muted mb-0"><?= htmlspecialchars((string) ($employee['linked_user_email'] ?: $employee['email']), ENT_QUOTES, 'UTF-8') ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($canManage): ?>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <button class="btn btn-outline-accent px-4 py-2" type="button" data-bs-toggle="collapse" data-bs-target="#employee-edit-<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>" aria-expanded="false" aria-controls="employee-edit-<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>">
                                        Bearbeiten
                                    </button>
                                    <form method="POST" action="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>/employees/<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>/delete" onsubmit="return confirm('Mitarbeiterprofil und zugehoerige Personalakten wirklich loeschen?');">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <button class="btn btn-outline-accent px-4 py-2" type="submit">Loeschen</button>
                                    </form>
                                </div>

                                <div class="collapse mb-3" id="employee-edit-<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <form method="POST" action="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>/employees/<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>/update">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="row g-3">
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold" for="position_title_<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>">Position</label>
                                                <input class="form-control" id="position_title_<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>" name="position_title" value="<?= htmlspecialchars((string) ($employee['position_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold" for="employment_status_<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>">Status</label>
                                                <select class="form-select" id="employment_status_<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>" name="employment_status">
                                                    <option value="active"<?= ($employee['employment_status'] ?? '') === 'active' ? ' selected' : '' ?>>Aktiv</option>
                                                    <option value="on_leave"<?= ($employee['employment_status'] ?? '') === 'on_leave' ? ' selected' : '' ?>>Beurlaubt</option>
                                                    <option value="inactive"<?= ($employee['employment_status'] ?? '') === 'inactive' ? ' selected' : '' ?>>Inaktiv</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold" for="hired_at_<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>">Eintrittsdatum</label>
                                                <input class="form-control" id="hired_at_<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>" name="hired_at" type="date" value="<?= htmlspecialchars((string) ($employee['hired_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-12 col-md-8">
                                                <label class="form-label fw-semibold" for="data_processing_basis_<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>">Rechtsgrundlage der Verarbeitung</label>
                                                <select class="form-select" id="data_processing_basis_<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>" name="data_processing_basis">
                                                    <option value="BDSG Paragraf 26 / DSGVO Art. 6 Abs. 1 lit. b"<?= ($employee['data_processing_basis'] ?? '') === 'BDSG Paragraf 26 / DSGVO Art. 6 Abs. 1 lit. b' ? ' selected' : '' ?>>BDSG Paragraf 26 / DSGVO Art. 6 Abs. 1 lit. b</option>
                                                    <option value="DSGVO Art. 6 Abs. 1 lit. c"<?= ($employee['data_processing_basis'] ?? '') === 'DSGVO Art. 6 Abs. 1 lit. c' ? ' selected' : '' ?>>DSGVO Art. 6 Abs. 1 lit. c</option>
                                                    <option value="DSGVO Art. 6 Abs. 1 lit. f"<?= ($employee['data_processing_basis'] ?? '') === 'DSGVO Art. 6 Abs. 1 lit. f' ? ' selected' : '' ?>>DSGVO Art. 6 Abs. 1 lit. f</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold" for="retention_until_<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>">Aufbewahrung bis</label>
                                                <input class="form-control" id="retention_until_<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>" name="retention_until" type="date" value="<?= htmlspecialchars((string) ($employee['retention_until'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold" for="personnel_rights_<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>">Oezlukrechte und Leistungen</label>
                                                <textarea class="form-control" id="personnel_rights_<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>" name="personnel_rights" rows="4"><?= htmlspecialchars((string) ($employee['personnel_rights'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold" for="notes_<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>">Notizen</label>
                                                <textarea class="form-control" id="notes_<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>" name="notes" rows="4"><?= htmlspecialchars((string) ($employee['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                            </div>
                                        </div>
                                        <button class="btn px-4 py-2 mt-4" type="submit">Aenderungen speichern</button>
                                    </form>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($employee['data_processing_basis']) || !empty($employee['retention_until'])): ?>
                                <div class="mb-3">
                                    <p class="eyebrow mb-2">Datenschutz</p>
                                    <p class="mb-0">
                                        <?php if (!empty($employee['data_processing_basis'])): ?>
                                            Rechtsgrundlage <?= htmlspecialchars((string) $employee['data_processing_basis'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                        <?php if (!empty($employee['retention_until'])): ?>
                                            <?php if (!empty($employee['data_processing_basis'])): ?> | <?php endif; ?>
                                            Aufbewahrung bis <?= htmlspecialchars((string) $employee['retention_until'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($employee['personnel_rights'])): ?>
                                <div class="mb-3">
                                    <p class="eyebrow mb-2">Oezlukrechte</p>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars((string) $employee['personnel_rights'], ENT_QUOTES, 'UTF-8')) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($employee['notes'])): ?>
                                <div class="mb-3">
                                    <p class="eyebrow mb-2">Notizen</p>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars((string) $employee['notes'], ENT_QUOTES, 'UTF-8')) ?></p>
                                </div>
                            <?php endif; ?>

                            <div>
                                <p class="eyebrow mb-2">Dateien</p>
                                <?php if (($employee['documents'] ?? []) === []): ?>
                                    <p class="muted mb-0">Noch keine Personalakten hochgeladen.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Datei</th>
                                                    <th>Groesse</th>
                                                    <th>Hochgeladen von</th>
                                                    <th>Aktion</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($employee['documents'] as $document): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars((string) $document['original_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                                        <td><?= htmlspecialchars((string) $document['file_size'], ENT_QUOTES, 'UTF-8') ?> B</td>
                                                        <td><?= htmlspecialchars((string) $document['uploaded_by_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                                        <td>
                                                            <a href="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>/employees/<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>/documents/<?= htmlspecialchars((string) $document['id'], ENT_QUOTES, 'UTF-8') ?>">
                                                                Download
                                                            </a>
                                                            <?php if ($canManage): ?>
                                                                <form class="d-inline" method="POST" action="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>/employees/<?= htmlspecialchars((string) $employee['id'], ENT_QUOTES, 'UTF-8') ?>/documents/<?= htmlspecialchars((string) $document['id'], ENT_QUOTES, 'UTF-8') ?>/delete" onsubmit="return confirm('Personalakte wirklich loeschen?');">
                                                                    <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                                    <button class="btn btn-outline-accent btn-sm px-3 py-2" type="submit">Loeschen</button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="card card-soft mt-4" id="department-filesystem">
    <p class="eyebrow">Filesystem</p>
    <h2 class="h4 mb-4">Dateien im Abteilungsordner</h2>
    <?php if ($shareFiles === []): ?>
        <p class="muted mb-0">Noch keine Dateien im lokalen Abteilungsordner.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Datei</th>
                        <th>Pfad</th>
                        <th>Groesse</th>
                        <th>Geaendert</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shareFiles as $file): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $file['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $file['path'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $file['size'], ENT_QUOTES, 'UTF-8') ?> B</td>
                            <td><?= htmlspecialchars((string) $file['modified_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a href="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>/files/open?path=<?= rawurlencode((string) $file['path']) ?>" target="_blank" rel="noreferrer">
                                    Oeffnen
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
