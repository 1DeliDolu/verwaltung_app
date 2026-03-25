<?php $title = 'Task bearbeiten'; ?>
<div class="hero">
    <p class="eyebrow">Task bearbeiten</p>
    <h1 class="display-6 fw-semibold"><?= htmlspecialchars((string) $task['title'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="lead">Passe Abteilung, Prioritaet, Zuweisung und Beschreibung an.</p>
</div>

<div class="card card-soft">
    <form method="POST" action="/tasks/<?= htmlspecialchars((string) $task['id'], ENT_QUOTES, 'UTF-8') ?>/update">
        <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <label class="form-label fw-semibold" for="department_id">Abteilung</label>
                <select class="form-select" id="department_id" name="department_id" required>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= htmlspecialchars((string) $department['id'], ENT_QUOTES, 'UTF-8') ?>" <?= (string) $task['department_id'] === (string) $department['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) $department['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-lg-6">
                <label class="form-label fw-semibold" for="assigned_to_user_id">Zugewiesen an</label>
                <select class="form-select" id="assigned_to_user_id" name="assigned_to_user_id">
                    <option value="">Nicht zuweisen</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold" for="title">Titel</label>
                <input class="form-control" id="title" name="title" required value="<?= htmlspecialchars((string) $task['title'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12 col-lg-6">
                <label class="form-label fw-semibold" for="priority">Prioritaet</label>
                <select class="form-select" id="priority" name="priority" required>
                    <?php foreach ($priorities as $priorityKey => $priorityLabel): ?>
                        <option value="<?= htmlspecialchars($priorityKey, ENT_QUOTES, 'UTF-8') ?>" <?= (string) $task['priority'] === $priorityKey ? 'selected' : '' ?>>
                            <?= htmlspecialchars($priorityLabel, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-lg-6">
                <label class="form-label fw-semibold" for="due_date">Faelligkeit</label>
                <input class="form-control" id="due_date" name="due_date" type="date" value="<?= htmlspecialchars((string) ($task['due_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold" for="description">Beschreibung</label>
                <textarea class="form-control" id="description" name="description" rows="8" required><?= htmlspecialchars((string) $task['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-4">
            <button class="btn px-4 py-2" type="submit">Aenderungen speichern</button>
            <a class="btn btn-outline-accent px-4 py-2" href="/tasks/<?= htmlspecialchars((string) $task['id'], ENT_QUOTES, 'UTF-8') ?>">Zurueck</a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const departmentSelect = document.getElementById('department_id');
        const assigneeSelect = document.getElementById('assigned_to_user_id');
        const assignableUsersMap = <?= json_encode($assignableUsersMap, JSON_UNESCAPED_SLASHES) ?>;
        const selectedAssignee = <?= json_encode((string) ($task['assigned_to_user_id'] ?? '')) ?>;

        const renderAssignees = function () {
            const departmentId = departmentSelect.value;
            const options = assignableUsersMap[departmentId] || [];

            assigneeSelect.innerHTML = '<option value="">Nicht zuweisen</option>';

            options.forEach(function (entry) {
                const option = document.createElement('option');
                option.value = String(entry.id);
                option.textContent = entry.name + ' | ' + entry.email;

                if (selectedAssignee !== '' && selectedAssignee === option.value) {
                    option.selected = true;
                }

                assigneeSelect.appendChild(option);
            });
        };

        departmentSelect.addEventListener('change', renderAssignees);
        renderAssignees();
    });
</script>
