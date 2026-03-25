<?php $title = 'Users'; ?>
<div class="hero">
    <p class="eyebrow">Admin</p>
    <h1 class="display-6 fw-semibold">Bereichsleiter Verzeichnis</h1>
    <p class="lead">Alle `leiter.*@verwaltung.local` Konten werden hier zentral fuer Demo, Login und Bereichszuordnung gelistet.</p>
</div>

<div class="card card-soft mb-4">
    <div class="row g-3 align-items-center">
        <div class="col-12 col-lg-8">
            <h2 class="h4 mb-2">Standardzugang fuer Bereichsleiter</h2>
            <p class="muted mb-0">Alle gelisteten Leiterkonten nutzen aktuell dasselbe Demo-Passwort.</p>
        </div>
        <div class="col-12 col-lg-4">
            <div class="dashboard-role-badge justify-content-center w-100">
                Passwort: <?= htmlspecialchars((string) $defaultLeaderPassword, ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    </div>
</div>

<div class="card card-soft">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Leiterkonten</p>
            <h2 class="h4 mb-0"><?= count($leaders) ?> Eintraege</h2>
        </div>
        <div class="dashboard-role-badge">
            Nur fuer Admin sichtbar
        </div>
    </div>

    <?php if ($leaders === []): ?>
        <p class="muted mb-0">Keine Bereichsleiter gefunden.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Abteilung</th>
                        <th scope="col">Name</th>
                        <th scope="col">E-Mail</th>
                        <th scope="col">Rolle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaders as $leader): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($leader['department_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($leader['name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><code><?= htmlspecialchars((string) ($leader['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td><?= htmlspecialchars((string) ($leader['membership_role'] ?? $leader['role_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
