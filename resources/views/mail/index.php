<?php $title = 'Mail'; ?>
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
    <div>
        <p class="eyebrow mb-2">Interne Kommunikation</p>
        <h1 class="display-6 fw-semibold mb-2">Mail fuer Teams und Abteilungen</h1>
        <p class="lead mb-0">Sende Nachrichten mit Vorlage, mehreren Empfaengern und Dokument-Anhang. Eingehende und gesendete Mails bleiben pro Benutzer getrennt.</p>
    </div>
    <div class="text-secondary small">
        Angemeldet als <strong><?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?></strong>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="row g-4 align-items-start">
    <div class="col-12 col-xl-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <p class="eyebrow mb-1">Compose</p>
                        <h2 class="h4 mb-0">Neue Nachricht</h2>
                    </div>
                    <span class="badge text-bg-primary rounded-pill">Template</span>
                </div>

                <form method="POST" action="/mail/send" enctype="multipart/form-data" class="d-flex flex-column gap-3">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <div>
                        <label class="form-label fw-semibold" for="recipient_emails">Empfaenger</label>
                        <select class="form-select" id="recipient_emails" name="recipient_emails[]" multiple size="8" required>
                            <?php foreach ($directory as $entry): ?>
                                <?php
                                $selected = in_array((string) ($entry['email'] ?? ''), $old['recipient_emails'] ?? [], true);
                                $departmentLabel = $entry['department_name'] ?? 'Management';
                                ?>
                                <option value="<?= htmlspecialchars((string) $entry['email'], ENT_QUOTES, 'UTF-8') ?>" <?= $selected ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $entry['name'], ENT_QUOTES, 'UTF-8') ?>
                                    | <?= htmlspecialchars((string) $departmentLabel, ENT_QUOTES, 'UTF-8') ?>
                                    | <?= htmlspecialchars((string) $entry['email'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Mehrfachauswahl mit `Ctrl` oder `Cmd`.</div>
                    </div>

                    <div>
                        <label class="form-label fw-semibold" for="subject">Betreff</label>
                        <input class="form-control" id="subject" name="subject" required value="<?= htmlspecialchars((string) ($old['subject'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div>
                        <label class="form-label fw-semibold" for="body">Nachricht</label>
                        <textarea class="form-control" id="body" name="body" rows="8" required><?= htmlspecialchars((string) ($old['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div>
                        <label class="form-label fw-semibold" for="attachment">Dokument</label>
                        <input class="form-control" id="attachment" name="attachment" type="file" accept=".pdf,.doc,.docx,.txt,.xlsx,.csv,.png,.jpg,.jpeg">
                        <div class="form-text">Optionaler Anhang fuer Richtlinien, PDFs oder Team-Unterlagen.</div>
                    </div>

                    <button class="btn btn-primary btn-lg" type="submit">Mail senden</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="row g-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <p class="eyebrow mb-1">Directory</p>
                                <h2 class="h4 mb-0">Interne Kontakte</h2>
                            </div>
                            <span class="badge text-bg-light rounded-pill"><?= count($directory) ?> Kontakte</span>
                        </div>
                        <div class="row g-3">
                            <?php foreach ($directory as $entry): ?>
                                <div class="col-12 col-md-6">
                                    <div class="border rounded-4 p-3 h-100 bg-white">
                                        <div class="fw-semibold"><?= htmlspecialchars((string) $entry['name'], ENT_QUOTES, 'UTF-8') ?></div>
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

            <div class="col-12">
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <p class="eyebrow mb-1">Inbox</p>
                                        <h2 class="h4 mb-0">Eingehend</h2>
                                    </div>
                                    <span class="badge text-bg-primary rounded-pill"><?= count($inbox) ?></span>
                                </div>
                                <?php if ($inbox === []): ?>
                                    <p class="text-secondary mb-0">Noch keine eingehenden Nachrichten.</p>
                                <?php else: ?>
                                    <div class="d-flex flex-column gap-3">
                                        <?php foreach ($inbox as $message): ?>
                                            <article class="border rounded-4 p-3 bg-white">
                                                <div class="small text-secondary mb-1">Von: <?= htmlspecialchars((string) $message['from'], ENT_QUOTES, 'UTF-8') ?></div>
                                                <h3 class="h6 mb-2"><?= htmlspecialchars((string) $message['subject'], ENT_QUOTES, 'UTF-8') ?></h3>
                                                <p class="mb-2"><?= nl2br(htmlspecialchars((string) $message['body'], ENT_QUOTES, 'UTF-8')) ?></p>
                                                <?php if (!empty($message['attachments'])): ?>
                                                    <div class="small mb-2">
                                                        <strong>Anhang:</strong> <?= htmlspecialchars((string) implode(', ', $message['attachments']), ENT_QUOTES, 'UTF-8') ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="small text-secondary"><?= htmlspecialchars((string) ($message['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <p class="eyebrow mb-1">Sent</p>
                                        <h2 class="h4 mb-0">Gesendet</h2>
                                    </div>
                                    <span class="badge text-bg-secondary rounded-pill"><?= count($sent) ?></span>
                                </div>
                                <?php if ($sent === []): ?>
                                    <p class="text-secondary mb-0">Noch keine gesendeten Nachrichten.</p>
                                <?php else: ?>
                                    <div class="d-flex flex-column gap-3">
                                        <?php foreach ($sent as $message): ?>
                                            <article class="border rounded-4 p-3 bg-white">
                                                <div class="small text-secondary mb-1">An: <?= htmlspecialchars((string) implode(', ', $message['to']), ENT_QUOTES, 'UTF-8') ?></div>
                                                <h3 class="h6 mb-2"><?= htmlspecialchars((string) $message['subject'], ENT_QUOTES, 'UTF-8') ?></h3>
                                                <p class="mb-2"><?= nl2br(htmlspecialchars((string) $message['body'], ENT_QUOTES, 'UTF-8')) ?></p>
                                                <?php if (!empty($message['attachments'])): ?>
                                                    <div class="small mb-2">
                                                        <strong>Anhang:</strong> <?= htmlspecialchars((string) implode(', ', $message['attachments']), ENT_QUOTES, 'UTF-8') ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="small text-secondary"><?= htmlspecialchars((string) ($message['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
