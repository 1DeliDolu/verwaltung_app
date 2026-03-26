<?php $title = 'Anmeldecode'; ?>
<div class="row justify-content-center py-4 py-md-5">
    <div class="col-12 col-md-10 col-lg-6">
        <div class="card card-soft">
            <h1 class="h2 mb-2">Anmeldecode bestaetigen</h1>
            <p class="muted mb-4">
                Wir haben einen Code an
                <strong><?= htmlspecialchars((string) $email, ENT_QUOTES, 'UTF-8') ?></strong>
                gesendet. Gib ihn ein, um die Anmeldung abzuschliessen.
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="/login/challenge">
                <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-4">
                    <label class="form-label fw-semibold" for="code">Code</label>
                    <input class="form-control" id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" required>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-3">
                    <button class="btn px-4 py-2" type="submit">Anmeldung abschliessen</button>
                    <a class="btn btn-outline-accent px-4 py-2" href="/login">Zur Anmeldung</a>
                </div>
            </form>
        </div>
    </div>
</div>
