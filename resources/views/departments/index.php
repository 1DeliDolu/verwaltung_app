<?php $title = 'Abteilungen'; ?>
<div class="hero">
    <p class="eyebrow">Abteilungen</p>
    <h1 class="display-6 fw-semibold">Dokumentenbereiche</h1>
    <p class="lead">Jede Abteilung hat ihren eigenen Ordnerbereich. Teamleiter verwalten Inhalte, Mitarbeiter lesen freigegebene Dokumente und sehen den fachlichen Fokus direkt in der Uebersicht.</p>
</div>

<div class="row g-4">
    <?php foreach ($departments as $department): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <a class="surface-link" href="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>">
                <div class="card card-soft h-100">
                    <p class="eyebrow"><?= htmlspecialchars((string) ($department['membership_role'] ?? $user['role_name'] ?? 'member'), ENT_QUOTES, 'UTF-8') ?></p>
                    <h2 class="h4"><?= htmlspecialchars((string) $department['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <?php if (!empty($department['tagline'])): ?>
                        <p class="mb-2"><?= htmlspecialchars((string) $department['tagline'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <p class="muted mb-3"><?= htmlspecialchars((string) ($department['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php if (!empty($department['focus'])): ?>
                        <p class="mb-0"><strong>Fokus:</strong> <?= htmlspecialchars((string) $department['focus'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
