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
</div>
