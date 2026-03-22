<?php $title = 'Login'; ?>
<div class="row justify-content-center py-4 py-md-5">
    <div class="col-12 col-md-10 col-lg-6">
        <div class="card card-soft">
            <h1 class="h2 mb-2">Anmeldung bei Verwaltung App</h1>
            <p class="muted mb-4">Melde dich mit dem Standard-Admin-Konto an.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="email">E-Mail</label>
                    <input class="form-control" id="email" name="email" type="email" required value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" for="password">Passwort</label>
                    <input class="form-control" id="password" name="password" type="password" required>
                </div>

                <button class="btn px-4 py-2" type="submit">Anmelden</button>
            </form>
        </div>
    </div>
</div>
