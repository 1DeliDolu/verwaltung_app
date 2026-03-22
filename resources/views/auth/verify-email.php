<?php $title = 'E-Mail-Verifizierung'; ?>
<div class="row justify-content-center py-4 py-md-5">
    <div class="col-12 col-lg-8">
        <div class="card card-soft">
            <p class="eyebrow">Verifizierung</p>
            <h1 class="h2 mb-2">Bitte bestaetige deine E-Mail-Adresse</h1>
            <p class="muted mb-4">
                Wir haben eine Verifizierungs-E-Mail an
                <strong><?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?></strong>
                gesendet. Oeffne den Link in MailHog und bestaetige dein Konto.
            </p>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div class="d-flex flex-column flex-md-row gap-3">
                <form method="POST" action="/email/verification-notification">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <button class="btn px-4 py-2" type="submit">Verifizierungs-E-Mail erneut senden</button>
                </form>
                <a class="btn btn-outline-accent px-4 py-2" href="http://127.0.0.1:8025" target="_blank" rel="noreferrer">MailHog oeffnen</a>
            </div>
        </div>
    </div>
</div>
