<?php $title = 'Login'; ?>
<div class="card" style="max-width: 480px; margin: 4rem auto 0;">
    <h1>Anmeldung bei Verwaltung App</h1>
    <p class="muted">Melde dich mit dem Standard-Admin-Konto an.</p>

    <?php if (!empty($error)): ?>
        <div class="flash flash-error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="/login">
        <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">

        <div class="field">
            <label for="email">E-Mail</label>
            <input id="email" name="email" type="email" required value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="field">
            <label for="password">Passwort</label>
            <input id="password" name="password" type="password" required>
        </div>

        <button class="btn" type="submit">Anmelden</button>
    </form>
</div>
