<?php $title = $department['name'] . ' Dokumente'; ?>
<div class="hero">
    <p class="eyebrow">Abteilung</p>
    <h1><?= htmlspecialchars((string) $department['name'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="lead"><?= htmlspecialchars((string) ($department['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
</div>

<?php if (!empty($success)): ?>
    <div class="flash flash-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="flash flash-error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($canManage): ?>
    <div class="card" style="margin-bottom: 1rem;">
        <h2>Neues Dokument anlegen</h2>
        <form method="POST" action="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>/documents">
            <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <div class="field">
                <label for="folder_name">Ordnername</label>
                <input id="folder_name" name="folder_name" required>
            </div>
            <div class="field">
                <label for="title">Dokumenttitel</label>
                <input id="title" name="title" required>
            </div>
            <div class="field">
                <label for="body">Inhalt</label>
                <textarea id="body" name="body" rows="6" style="width: 100%; padding: 0.8rem 0.9rem; border-radius: 12px; border: 1px solid var(--border); background: #fff; font-size: 1rem;" required></textarea>
            </div>
            <button class="btn" type="submit">Dokument speichern</button>
        </form>
    </div>
<?php endif; ?>

<div class="grid">
    <?php foreach ($documents as $document): ?>
        <article class="card">
            <p class="eyebrow"><?= htmlspecialchars((string) $document['folder_name'], ENT_QUOTES, 'UTF-8') ?></p>
            <h2><?= htmlspecialchars((string) $document['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= nl2br(htmlspecialchars((string) $document['body'], ENT_QUOTES, 'UTF-8')) ?></p>
            <p class="muted">Erstellt von <?= htmlspecialchars((string) $document['created_by_name'], ENT_QUOTES, 'UTF-8') ?></p>
        </article>
    <?php endforeach; ?>
</div>
