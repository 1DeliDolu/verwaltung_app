<?php $title = $department['name'] . ' Dokumente'; ?>
<div class="hero">
    <p class="eyebrow">Abteilung</p>
    <h1 class="display-6 fw-semibold"><?= htmlspecialchars((string) $department['name'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="lead"><?= htmlspecialchars((string) ($department['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($canManage): ?>
    <div class="card card-soft mb-4">
        <h2 class="h4 mb-4">Neues Dokument anlegen</h2>
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

    <div class="card card-soft mb-4">
        <h2 class="h4 mb-4">Datei in Abteilungsordner hochladen</h2>
        <form method="POST" action="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>/upload" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-4">
                <label class="form-label fw-semibold" for="upload_file">Datei</label>
                <input class="form-control" id="upload_file" name="upload_file" type="file" required>
            </div>
            <button class="btn px-4 py-2" type="submit">Datei hochladen</button>
        </form>
    </div>
<?php endif; ?>

<div class="row g-4">
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

<div class="card card-soft mt-4">
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shareFiles as $file): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $file['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $file['path'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) $file['size'], ENT_QUOTES, 'UTF-8') ?> B</td>
                            <td><?= htmlspecialchars((string) $file['modified_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
