<?php $title = 'Dashboard'; ?>
<div class="topbar">
    <div>
        <h1>Department Operations Portal</h1>
        <p class="muted">Uebersicht fuer angemeldete Benutzer.</p>
    </div>
    <form method="POST" action="/logout">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($app->session()->get('_csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
        <button class="btn" type="submit">Abmelden</button>
    </form>
</div>

<div class="card">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <p><strong>Willkommen, <?= htmlspecialchars((string) ($user['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></p>
    <p>E-Mail: <?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    <p>Rolle: <?= htmlspecialchars((string) ($user['role_name'] ?? 'employee'), ENT_QUOTES, 'UTF-8') ?></p>
</div>

<div class="grid" style="margin-top: 1rem;">
    <a class="card" style="text-decoration: none;" href="/services">
        <p class="eyebrow">Infrastruktur</p>
        <h2>Mail- und Dateiserver</h2>
        <p>Geplante und aktive interne Dienste mit Verantwortlichkeiten.</p>
    </a>
    <a class="card" style="text-decoration: none;" href="/departments">
        <p class="eyebrow">Abteilungen</p>
        <h2>Dokumentenordner</h2>
        <p>Teamleiter verwalten Inhalte, Mitarbeiter lesen freigegebene Dokumente.</p>
    </a>
</div>
