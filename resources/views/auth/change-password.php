<?php $title = 'Passwort aendern'; ?>
<div class="row justify-content-center py-4 py-md-5">
    <div class="col-12 col-md-10 col-lg-7">
        <div class="card card-soft">
            <h1 class="h2 mb-2">Passwortwechsel erforderlich</h1>
            <p class="muted mb-4">Beim ersten Login muss das temporaere Passwort ersetzt werden. Das neue Passwort muss mindestens 12 Zeichen lang sein und Grossbuchstaben, Kleinbuchstaben, Zahlen sowie Sonderzeichen enthalten.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="/password/change">
                <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="current_password">Aktuelles Passwort</label>
                    <input class="form-control" id="current_password" name="current_password" type="password" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="new_password">Neues Passwort</label>
                    <input class="form-control" id="new_password" name="new_password" type="password" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" for="new_password_confirmation">Neues Passwort bestaetigen</label>
                    <input class="form-control" id="new_password_confirmation" name="new_password_confirmation" type="password" required>
                </div>

                <button class="btn px-4 py-2" type="submit">Passwort aktualisieren</button>
            </form>
        </div>
    </div>
</div>
