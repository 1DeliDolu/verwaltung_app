<?php $title = 'Interne Mail'; ?>
<div class="hero">
    <p class="eyebrow">Interne Kommunikation</p>
    <h1 class="display-6 fw-semibold">Mail zwischen Abteilungen und Mitarbeitern</h1>
    <p class="lead">Waehle interne Empfaenger, sende Nachrichten ueber MailHog und pruefe eingehende sowie gesendete Demo-Mails direkt im Portal.</p>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-12 col-xl-5">
        <div class="card card-soft h-100">
            <p class="eyebrow">Neue Nachricht</p>
            <h2 class="h4 mb-4">Interne Mail verfassen</h2>
            <form method="POST" action="/mail/send">
                <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="recipient_email">Empfaenger</label>
                    <select class="form-select" id="recipient_email" name="recipient_email" required>
                        <option value="">Bitte waehlen</option>
                        <?php foreach ($directory as $entry): ?>
                            <?php
                            $selected = ($old['recipient_email'] ?? '') === ($entry['email'] ?? '');
                            $departmentLabel = $entry['department_name'] ?? 'Management';
                            ?>
                            <option value="<?= htmlspecialchars((string) $entry['email'], ENT_QUOTES, 'UTF-8') ?>" <?= $selected ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $entry['name'], ENT_QUOTES, 'UTF-8') ?>
                                (<?= htmlspecialchars((string) $entry['email'], ENT_QUOTES, 'UTF-8') ?>)
                                - <?= htmlspecialchars((string) $departmentLabel, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="subject">Betreff</label>
                    <input class="form-control" id="subject" name="subject" required value="<?= htmlspecialchars((string) ($old['subject'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" for="body">Nachricht</label>
                    <textarea class="form-control" id="body" name="body" rows="7" required><?= htmlspecialchars((string) ($old['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <button class="btn px-4 py-2" type="submit">Interne Mail senden</button>
            </form>
        </div>
    </div>

    <div class="col-12 col-xl-7">
        <div class="card card-soft h-100">
            <p class="eyebrow">Verzeichnis</p>
            <h2 class="h4 mb-4">Interne Kontakte</h2>
            <div class="row g-3">
                <?php foreach ($directory as $entry): ?>
                    <div class="col-12 col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-white">
                            <strong><?= htmlspecialchars((string) $entry['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <div class="small text-secondary"><?= htmlspecialchars((string) $entry['email'], ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="small mt-2"><?= htmlspecialchars((string) ($entry['department_name'] ?? 'Management'), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="small text-secondary"><?= htmlspecialchars((string) ($entry['membership_role'] ?? $entry['role_name'] ?? 'member'), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12 col-lg-6">
        <div class="card card-soft h-100">
            <p class="eyebrow">Eingang</p>
            <h2 class="h4 mb-4">Eingehende Nachrichten fuer <?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?></h2>
            <?php if ($inbox === []): ?>
                <p class="muted mb-0">Noch keine eingehenden Nachrichten.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($inbox as $message): ?>
                        <article class="border rounded-4 p-3 bg-white">
                            <div class="small text-secondary mb-1">Von: <?= htmlspecialchars((string) $message['from'], ENT_QUOTES, 'UTF-8') ?></div>
                            <h3 class="h6 mb-2"><?= htmlspecialchars((string) $message['subject'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="mb-2"><?= nl2br(htmlspecialchars((string) $message['body'], ENT_QUOTES, 'UTF-8')) ?></p>
                            <div class="small text-secondary"><?= htmlspecialchars((string) ($message['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card card-soft h-100">
            <p class="eyebrow">Gesendet</p>
            <h2 class="h4 mb-4">Ausgehende Nachrichten von <?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?></h2>
            <?php if ($sent === []): ?>
                <p class="muted mb-0">Noch keine gesendeten Nachrichten.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($sent as $message): ?>
                        <article class="border rounded-4 p-3 bg-white">
                            <div class="small text-secondary mb-1">An: <?= htmlspecialchars((string) implode(', ', $message['to']), ENT_QUOTES, 'UTF-8') ?></div>
                            <h3 class="h6 mb-2"><?= htmlspecialchars((string) $message['subject'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="mb-2"><?= nl2br(htmlspecialchars((string) $message['body'], ENT_QUOTES, 'UTF-8')) ?></p>
                            <div class="small text-secondary"><?= htmlspecialchars((string) ($message['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
