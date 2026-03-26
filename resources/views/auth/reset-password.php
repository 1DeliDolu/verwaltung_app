<?php $title = 'Passwort zuruecksetzen'; ?>
<div class="row justify-content-center py-4 py-md-5">
    <div class="col-12 col-md-10 col-lg-6">
        <div class="card card-soft">
            <h1 class="h2 mb-2">Neues Passwort setzen</h1>
            <p class="muted mb-4">
                Setze ein neues Passwort fuer
                <strong><?= htmlspecialchars((string) $email, ENT_QUOTES, 'UTF-8') ?></strong>.
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="/password/reset/<?= rawurlencode((string) $token) ?>">
                <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="password">Neues Passwort</label>
                    <input class="form-control" id="password" name="password" type="password" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" for="password_confirmation">Passwort bestaetigen</label>
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-3">
                    <button class="btn px-4 py-2" type="submit">Passwort speichern</button>
                    <a class="btn btn-outline-accent px-4 py-2" href="/login">Zur Anmeldung</a>
                </div>
            </form>
        </div>
    </div>
</div>
