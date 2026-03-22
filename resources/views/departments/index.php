<?php $title = 'Abteilungen'; ?>
<div class="hero">
    <p class="eyebrow">Abteilungen</p>
    <h1>Dokumentenbereiche</h1>
    <p class="lead">Jede Abteilung hat ihren eigenen Ordnerbereich. Teamleiter verwalten Inhalte, Mitarbeiter lesen freigegebene Dokumente.</p>
</div>

<div class="grid">
    <?php foreach ($departments as $department): ?>
        <a class="card" style="text-decoration: none;" href="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>">
            <p class="eyebrow"><?= htmlspecialchars((string) ($department['membership_role'] ?? $user['role_name'] ?? 'member'), ENT_QUOTES, 'UTF-8') ?></p>
            <h2><?= htmlspecialchars((string) $department['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars((string) ($department['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        </a>
    <?php endforeach; ?>
</div>
