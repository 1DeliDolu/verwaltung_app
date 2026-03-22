<?php
$title = 'Mail';
$pageClass = 'page-mail';

$renderSnippet = static function (string $text): string {
    $singleLine = preg_replace('/\s+/', ' ', trim($text)) ?? '';

    if (strlen($singleLine) <= 92) {
        return $singleLine;
    }

    return substr($singleLine, 0, 89) . '...';
};
?>

<style>
    .mail-workspace {
        min-height: calc(100vh - 1rem);
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .mail-topbar {
        display: grid;
        grid-template-columns: auto auto minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: center;
    }
    .mail-brand {
        display: inline-flex;
        align-items: center;
        gap: 0.9rem;
        color: #f3f4f6;
        text-decoration: none;
        padding-right: 0.5rem;
    }
    .mail-brand-logo {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #ea4335 0%, #fbbc04 34%, #34a853 67%, #4285f4 100%);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.28);
    }
    .mail-brand-name {
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: -0.03em;
    }
    .mail-menu-button,
    .mail-toolbar-button,
    .mail-folder-button {
        border: 0;
        background: transparent;
        color: #cbd5e1;
    }
    .mail-menu-button,
    .mail-toolbar-button {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
    .mail-menu-button:hover,
    .mail-toolbar-button:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
    }
    .mail-search {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        background: #2d2f31;
        border-radius: 28px;
        padding: 0.8rem 1.25rem;
        color: #9ca3af;
    }
    .mail-search input {
        width: 100%;
        background: transparent;
        border: 0;
        color: #f3f4f6;
        font-size: 1.05rem;
        outline: none;
    }
    .mail-toolbar {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.3rem;
    }
    .mail-body {
        display: grid;
        grid-template-columns: 260px minmax(0, 1fr) 360px;
        gap: 1rem;
        align-items: start;
    }
    .mail-sidebar {
        padding-top: 0.25rem;
    }
    .mail-compose-trigger {
        width: 100%;
        display: inline-flex;
        align-items: center;
        gap: 0.9rem;
        border: 0;
        border-radius: 20px;
        background: #f8fafc;
        color: #4b5563;
        padding: 1.05rem 1.4rem;
        font-size: 1.05rem;
        font-weight: 700;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
        margin-bottom: 0.8rem;
    }
    .mail-folder-list,
    .mail-mini-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .mail-folder-item a,
    .mail-folder-item button {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 0.72rem 1rem;
        border-radius: 0 999px 999px 0;
        color: #e5e7eb;
        text-decoration: none;
        font-weight: 600;
        background: transparent;
        border: 0;
        text-align: left;
    }
    .mail-folder-item.active a,
    .mail-folder-item.active button,
    .mail-folder-item a:hover,
    .mail-folder-item button:hover {
        background: #545454;
        color: #fff;
    }
    .mail-folder-count {
        margin-left: auto;
        color: #f3f4f6;
        font-size: 0.95rem;
    }
    .mail-sidebar-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 1.6rem 0 0.9rem;
        color: #f3f4f6;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .mail-main {
        background: #2b2b2b;
        border: 1px solid #3b3b3b;
        border-radius: 28px;
        overflow: hidden;
    }
    .mail-main-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.2rem;
        border-bottom: 1px solid #454545;
        color: #d1d5db;
    }
    .mail-main-toolbar-left,
    .mail-main-toolbar-right {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .mail-tab-nav {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        border-bottom: 1px solid #454545;
        background: #2b2b2b;
    }
    .mail-tab-button {
        border: 0;
        background: transparent;
        color: #c4c7c5;
        display: inline-flex;
        align-items: center;
        gap: 0.7rem;
        justify-content: flex-start;
        padding: 1rem 1.2rem;
        font-size: 0.95rem;
        font-weight: 700;
        border-bottom: 3px solid transparent;
    }
    .mail-tab-button.active {
        color: #f8fafc;
        border-bottom-color: #8ab4f8;
    }
    .mail-tab-button .badge {
        background: #f3f4f6;
        color: #111827;
    }
    .mail-list {
        display: flex;
        flex-direction: column;
    }
    .mail-list-header,
    .mail-row {
        display: grid;
        grid-template-columns: 250px minmax(0, 1fr) 112px;
        gap: 1rem;
        align-items: center;
    }
    .mail-list-header {
        padding: 0.9rem 1.4rem;
        color: #9ca3af;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        border-bottom: 1px solid #454545;
    }
    .mail-row {
        padding: 0.95rem 1.4rem;
        color: #f3f4f6;
        border-bottom: 1px solid #3e3e3e;
        transition: background 140ms ease;
    }
    .mail-row:hover {
        background: #343434;
    }
    .mail-row-meta {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        min-width: 0;
    }
    .mail-check {
        width: 20px;
        height: 20px;
        border: 2px solid #8a8d91;
        border-radius: 4px;
        flex: 0 0 auto;
    }
    .mail-star {
        color: #9ca3af;
        font-size: 1.05rem;
        flex: 0 0 auto;
    }
    .mail-from,
    .mail-recipient {
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .mail-row-content {
        min-width: 0;
        display: flex;
        align-items: baseline;
        gap: 0.55rem;
        white-space: nowrap;
        overflow: hidden;
    }
    .mail-row-subject {
        font-size: 1.05rem;
        font-weight: 700;
        color: #f9fafb;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .mail-row-snippet {
        color: #9ca3af;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .mail-attachment {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.35rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        background: #3f3f46;
        color: #e5e7eb;
        font-size: 0.8rem;
    }
    .mail-row-time {
        text-align: right;
        color: #f3f4f6;
        font-weight: 700;
    }
    .mail-empty {
        padding: 2rem 1.4rem;
        color: #9ca3af;
    }
    .mail-compose {
        background: #ffffff;
        color: #0f172a;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 50px rgba(0, 0, 0, 0.34);
        border: 1px solid #dbe4f0;
    }
    .mail-compose-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        background: #f3f4f6;
        border-bottom: 1px solid #e5e7eb;
    }
    .mail-compose-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0b3b82;
    }
    .mail-compose-window-actions {
        display: inline-flex;
        gap: 0.35rem;
        color: #4b5563;
    }
    .mail-compose-window-actions span {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .mail-compose-window-actions span:hover {
        background: #e5e7eb;
    }
    .mail-compose form {
        display: flex;
        flex-direction: column;
        min-height: 620px;
    }
    .mail-line {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .mail-line label {
        min-width: 58px;
        font-size: 0.95rem;
        color: #374151;
        padding-top: 0.35rem;
    }
    .mail-line select,
    .mail-line input,
    .mail-compose textarea {
        width: 100%;
        border: 0;
        outline: none;
        background: transparent;
        color: #0f172a;
    }
    .mail-line select {
        min-height: 88px;
        resize: none;
    }
    .mail-compose textarea {
        flex: 1;
        min-height: 260px;
        padding: 1rem 1.25rem;
        resize: none;
    }
    .mail-compose-toolbar {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        flex-wrap: wrap;
        padding: 0.7rem 1.1rem;
        border-top: 1px solid #e5e7eb;
        background: #eef2ff;
    }
    .mail-compose-toolbar span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        border-radius: 18px;
        color: #4b5563;
    }
    .mail-compose-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
        padding: 1rem 1.25rem 1.2rem;
    }
    .mail-send-button {
        display: inline-flex;
        align-items: center;
        border: 0;
        border-radius: 999px;
        overflow: hidden;
        background: #1a73e8;
        color: #fff;
        font-size: 1.05rem;
        font-weight: 700;
    }
    .mail-send-button span {
        padding: 0.85rem 1.65rem;
    }
    .mail-send-button strong {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        border-left: 1px solid rgba(255, 255, 255, 0.28);
    }
    .mail-compose-utility {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        color: #4b5563;
    }
    .mail-compose-utility span {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .mail-compose-utility span:hover {
        background: #eef2ff;
    }
    .mail-attachment-field {
        padding: 0 1.25rem 1rem;
    }
    .mail-attachment-field input {
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 0.7rem 0.85rem;
        width: 100%;
    }
    .mail-attachment-help {
        margin-top: 0.45rem;
        font-size: 0.82rem;
        color: #6b7280;
    }
    @media (max-width: 1399px) {
        .mail-body {
            grid-template-columns: 240px minmax(0, 1fr);
        }
        .mail-compose-column {
            grid-column: 1 / -1;
        }
    }
    @media (max-width: 991px) {
        .mail-topbar {
            grid-template-columns: auto minmax(0, 1fr);
        }
        .mail-toolbar {
            grid-column: 1 / -1;
            justify-content: flex-start;
        }
        .mail-body {
            grid-template-columns: 1fr;
        }
        .mail-list-header,
        .mail-row {
            grid-template-columns: 1fr;
        }
        .mail-row-time {
            text-align: left;
        }
    }
</style>

<div class="mail-workspace">
    <div class="mail-topbar">
        <button class="mail-menu-button" type="button" aria-label="Menue">|||</button>
        <a class="mail-brand" href="/dashboard">
            <span class="mail-brand-logo" aria-hidden="true"></span>
            <span class="mail-brand-name">Gmail</span>
        </a>
        <div class="mail-search">
            <span>Suche</span>
            <input type="search" value="" placeholder="In E-Mails suchen">
            <span>Filter</span>
        </div>
        <div class="mail-toolbar">
            <button class="mail-toolbar-button" type="button" aria-label="Hilfe">?</button>
            <button class="mail-toolbar-button" type="button" aria-label="Einstellungen">[]</button>
            <button class="mail-toolbar-button" type="button" aria-label="Profil"><?= htmlspecialchars(substr((string) $user['name'], 0, 1), ENT_QUOTES, 'UTF-8') ?></button>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success mb-0"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger mb-0"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="mail-body">
        <aside class="mail-sidebar">
            <button class="mail-compose-trigger" type="button">
                <span>[+]</span>
                <span>Schreiben</span>
            </button>

            <ul class="mail-folder-list">
                <li class="mail-folder-item active"><button type="button">Posteingang <span class="mail-folder-count"><?= count($inbox) ?></span></button></li>
                <li class="mail-folder-item"><button type="button">Markiert</button></li>
                <li class="mail-folder-item"><button type="button">Zurueckgestellt</button></li>
                <li class="mail-folder-item"><button type="button">Gesendet <span class="mail-folder-count"><?= count($sent) ?></span></button></li>
                <li class="mail-folder-item"><button type="button">Entwuerfe</button></li>
                <li class="mail-folder-item"><button type="button">Mehr</button></li>
            </ul>

            <div class="mail-sidebar-heading">
                <span>Labels</span>
                <button class="mail-folder-button" type="button">+</button>
            </div>
            <ul class="mail-mini-list">
                <li class="mail-folder-item"><button type="button">Abteilungen <span class="mail-folder-count"><?= count($directory) ?></span></button></li>
                <li class="mail-folder-item"><button type="button">Probe Dateien</button></li>
            </ul>
        </aside>

        <main class="mail-main">
            <div class="mail-main-toolbar">
                <div class="mail-main-toolbar-left">
                    <button class="mail-toolbar-button" type="button" aria-label="Auswaehlen">[]</button>
                    <button class="mail-toolbar-button" type="button" aria-label="Aktualisieren">R</button>
                    <button class="mail-toolbar-button" type="button" aria-label="Mehr">...</button>
                </div>
                <div class="mail-main-toolbar-right">
                    <span><?= count($inbox) + count($sent) ?> Nachrichten</span>
                    <button class="mail-toolbar-button" type="button" aria-label="Zurueck"><</button>
                    <button class="mail-toolbar-button" type="button" aria-label="Weiter">></button>
                </div>
            </div>

            <div class="mail-tab-nav" id="mailTabs" role="tablist">
                <button class="mail-tab-button active" id="inbox-tab" data-bs-toggle="tab" data-bs-target="#mail-inbox" type="button" role="tab" aria-controls="mail-inbox" aria-selected="true">
                    <span>Posteingang</span>
                    <span class="badge rounded-pill"><?= count($inbox) ?></span>
                </button>
                <button class="mail-tab-button" id="sent-tab" data-bs-toggle="tab" data-bs-target="#mail-sent" type="button" role="tab" aria-controls="mail-sent" aria-selected="false">
                    <span>Gesendet</span>
                    <span class="badge rounded-pill"><?= count($sent) ?></span>
                </button>
                <button class="mail-tab-button" id="team-tab" data-bs-toggle="tab" data-bs-target="#mail-team" type="button" role="tab" aria-controls="mail-team" aria-selected="false">
                    <span>Team</span>
                    <span class="badge rounded-pill"><?= count($directory) ?></span>
                </button>
            </div>

            <div class="tab-content">
                <section class="tab-pane fade show active" id="mail-inbox" role="tabpanel" aria-labelledby="inbox-tab">
                    <div class="mail-list">
                        <div class="mail-list-header">
                            <div>Sender</div>
                            <div>Betreff</div>
                            <div>Zeit</div>
                        </div>
                        <?php if ($inbox === []): ?>
                            <div class="mail-empty">Keine Nachrichten im Posteingang.</div>
                        <?php else: ?>
                            <?php foreach ($inbox as $message): ?>
                                <article class="mail-row">
                                    <div class="mail-row-meta">
                                        <span class="mail-check" aria-hidden="true"></span>
                                        <span class="mail-star" aria-hidden="true">*</span>
                                        <span class="mail-from"><?= htmlspecialchars((string) $message['from'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <div>
                                        <div class="mail-row-content">
                                            <span class="mail-row-subject"><?= htmlspecialchars((string) $message['subject'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <span class="mail-row-snippet">- <?= htmlspecialchars($renderSnippet((string) $message['body']), ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                        <?php if (!empty($message['attachments'])): ?>
                                            <div class="mail-attachment">Dokument: <?= htmlspecialchars((string) implode(', ', $message['attachments']), ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mail-row-time"><?= htmlspecialchars((string) ($message['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="tab-pane fade" id="mail-sent" role="tabpanel" aria-labelledby="sent-tab">
                    <div class="mail-list">
                        <div class="mail-list-header">
                            <div>Empfaenger</div>
                            <div>Betreff</div>
                            <div>Zeit</div>
                        </div>
                        <?php if ($sent === []): ?>
                            <div class="mail-empty">Noch keine gesendeten Nachrichten.</div>
                        <?php else: ?>
                            <?php foreach ($sent as $message): ?>
                                <article class="mail-row">
                                    <div class="mail-row-meta">
                                        <span class="mail-check" aria-hidden="true"></span>
                                        <span class="mail-star" aria-hidden="true">*</span>
                                        <span class="mail-recipient"><?= htmlspecialchars((string) implode(', ', $message['to']), ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <div>
                                        <div class="mail-row-content">
                                            <span class="mail-row-subject"><?= htmlspecialchars((string) $message['subject'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <span class="mail-row-snippet">- <?= htmlspecialchars($renderSnippet((string) $message['body']), ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                        <?php if (!empty($message['attachments'])): ?>
                                            <div class="mail-attachment">Dokument: <?= htmlspecialchars((string) implode(', ', $message['attachments']), ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mail-row-time"><?= htmlspecialchars((string) ($message['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="tab-pane fade" id="mail-team" role="tabpanel" aria-labelledby="team-tab">
                    <div class="mail-list">
                        <div class="mail-list-header">
                            <div>Kontakt</div>
                            <div>Abteilung</div>
                            <div>Rolle</div>
                        </div>
                        <?php foreach ($directory as $entry): ?>
                            <article class="mail-row">
                                <div class="mail-row-meta">
                                    <span class="mail-check" aria-hidden="true"></span>
                                    <span class="mail-star" aria-hidden="true">*</span>
                                    <span class="mail-from"><?= htmlspecialchars((string) $entry['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div>
                                    <div class="mail-row-content">
                                        <span class="mail-row-subject"><?= htmlspecialchars((string) ($entry['department_name'] ?? 'Management'), ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="mail-row-snippet">- <?= htmlspecialchars((string) $entry['email'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>
                                <div class="mail-row-time"><?= htmlspecialchars((string) ($entry['membership_role'] ?? $entry['role_name'] ?? 'member'), ENT_QUOTES, 'UTF-8') ?></div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </main>

        <aside class="mail-compose-column">
            <section class="mail-compose">
                <div class="mail-compose-header">
                    <div class="mail-compose-title">Neue Nachricht</div>
                    <div class="mail-compose-window-actions">
                        <span>-</span>
                        <span>[]</span>
                        <span>x</span>
                    </div>
                </div>

                <form method="POST" action="/mail/send" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mail-line">
                        <label for="recipient_emails">An</label>
                        <div class="w-100">
                            <select id="recipient_emails" name="recipient_emails[]" multiple required>
                                <?php foreach ($directory as $entry): ?>
                                    <?php $selected = in_array((string) ($entry['email'] ?? ''), $old['recipient_emails'] ?? [], true); ?>
                                    <option value="<?= htmlspecialchars((string) $entry['email'], ENT_QUOTES, 'UTF-8') ?>" <?= $selected ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $entry['name'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars((string) $entry['email'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="mail-attachment-help">Mehrfachauswahl mit `Ctrl` oder `Cmd`. Cc und Bcc werden in der Demo nicht separat gespeichert.</div>
                        </div>
                    </div>

                    <div class="mail-line">
                        <label for="subject">Betreff</label>
                        <input id="subject" name="subject" required value="<?= htmlspecialchars((string) ($old['subject'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <textarea id="body" name="body" required placeholder="Nachricht verfassen"><?= htmlspecialchars((string) ($old['body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>

                    <div class="mail-compose-toolbar">
                        <span>Sans</span>
                        <span>T</span>
                        <span>B</span>
                        <span>I</span>
                        <span>U</span>
                        <span>A</span>
                        <span>List</span>
                    </div>

                    <div class="mail-attachment-field">
                        <input id="attachment" name="attachment" type="file" accept=".pdf,.doc,.docx,.txt,.xlsx,.csv,.png,.jpg,.jpeg">
                        <div class="mail-attachment-help">Dokumente koennen direkt an interne Nachrichten angehaengt werden.</div>
                    </div>

                    <div class="mail-compose-actions">
                        <button class="mail-send-button" type="submit">
                            <span>Senden</span>
                            <strong>v</strong>
                        </button>

                        <div class="mail-compose-utility">
                            <span>Aa</span>
                            <span>@</span>
                            <span>[]</span>
                            <span>#</span>
                            <span>...</span>
                        </div>
                    </div>
                </form>
            </section>
        </aside>
    </div>
</div>
