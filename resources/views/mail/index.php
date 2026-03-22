<?php
$title = 'Mail';
$pageClass = 'page-mail';
$filters = $filters ?? ['term' => '', 'scope' => ['all']];
$markedMessages = array_values(array_filter(
    array_merge($inbox, $sent),
    static fn (array $message): bool => !empty($message['attachments'])
));

$renderSnippet = static function (string $text): string {
    $singleLine = preg_replace('/\s+/', ' ', trim($text)) ?? '';

    if (strlen($singleLine) <= 92) {
        return $singleLine;
    }

    return substr($singleLine, 0, 89) . '...';
};

$buildDetailPayload = static function (array $message, string $folder): array {
    $attachmentNames = array_map(
        static fn (array $attachment): string => (string) ($attachment['name'] ?? ''),
        $message['attachments'] ?? []
    );

    $attachmentLinks = array_map(
        static fn (array $attachment): string => sprintf(
            '<a href="%s" class="mail-download-link">Download: %s</a>',
            htmlspecialchars((string) ($attachment['download_url'] ?? '#'), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars((string) ($attachment['name'] ?? ''), ENT_QUOTES, 'UTF-8')
        ),
        $message['attachments'] ?? []
    );

    return [
        'folder' => $folder,
        'subject' => (string) ($message['subject'] ?? ''),
        'body' => (string) ($message['body'] ?? ''),
        'from' => (string) ($message['from'] ?? ''),
        'to' => implode(', ', $message['to'] ?? []),
        'created_at' => (string) ($message['created_at'] ?? ''),
        'attachments' => implode(', ', array_filter($attachmentNames)),
        'attachments_html' => $attachmentLinks === [] ? '-' : implode(' ', $attachmentLinks),
    ];
};

$initialDetail = null;

if ($inbox !== []) {
    $initialDetail = $buildDetailPayload($inbox[0], 'Posteingang');
} elseif ($sent !== []) {
    $initialDetail = $buildDetailPayload($sent[0], 'Gesendet');
} elseif ($markedMessages !== []) {
    $initialDetail = $buildDetailPayload($markedMessages[0], 'Markiert');
}
?>

<style>
    .mail-workspace {
        min-height: calc(100vh - 8rem);
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
        color: #1f2933;
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
        color: #6b7280;
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
        background: rgba(166, 61, 64, 0.1);
        color: #1f2933;
    }
    .mail-search {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        background: rgba(255, 253, 248, 0.9);
        border: 1px solid #e7d8bf;
        border-radius: 28px;
        padding: 0.8rem 1.25rem;
        color: #6b7280;
        box-shadow: 0 14px 30px rgba(59, 41, 25, 0.08);
    }
    .mail-search input {
        width: 100%;
        background: transparent;
        border: 0;
        color: #1f2933;
        font-size: 1.05rem;
        outline: none;
    }
    .mail-search button,
    .mail-search select {
        border: 0;
        background: transparent;
        color: #6b7280;
        outline: none;
    }
    .mail-search select {
        min-width: 120px;
    }
    .mail-search-filter {
        position: relative;
    }
    .mail-search-filter summary {
        list-style: none;
        cursor: pointer;
        color: #6b7280;
        font-weight: 600;
        white-space: nowrap;
    }
    .mail-search-filter summary::-webkit-details-marker {
        display: none;
    }
    .mail-search-filter[open] summary {
        color: #1f2933;
    }
    .mail-search-menu {
        position: absolute;
        top: calc(100% + 0.65rem);
        right: 0;
        min-width: 220px;
        padding: 0.85rem 1rem;
        background: #fffdf8;
        border: 1px solid #e7d8bf;
        border-radius: 18px;
        box-shadow: 0 18px 40px rgba(59, 41, 25, 0.14);
        display: grid;
        gap: 0.65rem;
        z-index: 20;
    }
    .mail-search-menu label {
        display: grid;
        grid-template-columns: 18px 1fr;
        align-items: center;
        justify-items: start;
        column-gap: 0.7rem;
        color: #6b7280;
        font-size: 0.92rem;
        text-align: left;
    }
    .mail-search-menu input[type="checkbox"] {
        margin: 0;
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
        background: #fffdf8;
        color: #1f2933;
        padding: 1.05rem 1.4rem;
        font-size: 1.05rem;
        font-weight: 700;
        border: 1px solid #e7d8bf;
        box-shadow: 0 18px 36px rgba(59, 41, 25, 0.12);
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
        color: #3f3f46;
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
        background: #efe3cd;
        color: #1f2933;
    }
    .mail-folder-count {
        margin-left: auto;
        color: #7f1d1d;
        font-size: 0.95rem;
    }
    .mail-sidebar-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 1.6rem 0 0.9rem;
        color: #1f2933;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .mail-main {
        background: rgba(255, 253, 248, 0.92);
        border: 1px solid #e7d8bf;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 18px 40px rgba(59, 41, 25, 0.1);
    }
    .mail-main-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.2rem;
        border-bottom: 1px solid #e7d8bf;
        color: #6b7280;
    }
    .mail-main-toolbar-left,
    .mail-main-toolbar-right {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .mail-tab-nav {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        border-bottom: 1px solid #e7d8bf;
        background: rgba(255, 251, 244, 0.9);
    }
    .mail-tab-button {
        border: 0;
        background: transparent;
        color: #6b7280;
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
        color: #1f2933;
        border-bottom-color: #a63d40;
        background: rgba(166, 61, 64, 0.05);
    }
    .mail-tab-button .badge {
        background: #efe3cd;
        color: #7f1d1d;
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
        color: #6b7280;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        border-bottom: 1px solid #e7d8bf;
    }
    .mail-row {
        padding: 0.95rem 1.4rem;
        color: #1f2933;
        border-bottom: 1px solid #f0e6d5;
        transition: background 140ms ease;
    }
    .mail-row:hover {
        background: #fff8ed;
    }
    .mail-row.is-selected {
        background: #f8f2e8;
        box-shadow: inset 4px 0 0 #a63d40;
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
        border: 2px solid #b29b79;
        border-radius: 4px;
        flex: 0 0 auto;
    }
    .mail-star {
        color: #a63d40;
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
        color: #1f2933;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .mail-row-snippet {
        color: #6b7280;
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
        background: #efe3cd;
        color: #7f1d1d;
        font-size: 0.8rem;
    }
    .mail-download-link {
        color: #7f1d1d;
        text-decoration: none;
        border-bottom: 1px solid rgba(127, 29, 29, 0.25);
    }
    .mail-download-link:hover {
        color: #a63d40;
        border-color: #a63d40;
    }
    .mail-row-time {
        text-align: right;
        color: #7f1d1d;
        font-weight: 700;
    }
    .mail-empty {
        padding: 2rem 1.4rem;
        color: #6b7280;
    }
    .mail-detail {
        border-top: 1px solid #e7d8bf;
        background: #fffaf2;
        padding: 1.3rem 1.4rem 1.5rem;
    }
    .mail-detail-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.9rem;
    }
    .mail-detail-folder {
        color: #a63d40;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 0.35rem;
    }
    .mail-detail-subject {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        color: #1f2933;
    }
    .mail-detail-meta {
        display: grid;
        gap: 0.35rem;
        color: #6b7280;
        font-size: 0.95rem;
        margin-bottom: 1rem;
    }
    .mail-detail-body {
        color: #1f2933;
        line-height: 1.75;
        white-space: normal;
    }
    .mail-message-modal .modal-content {
        border-radius: 22px;
        border: 1px solid #e7d8bf;
        background: #fffdf8;
        box-shadow: 0 24px 60px rgba(59, 41, 25, 0.22);
    }
    .mail-message-modal .modal-header,
    .mail-message-modal .modal-footer {
        border-color: #e7d8bf;
        background: #f8f2e8;
    }
    .mail-message-modal .modal-title {
        color: #7f1d1d;
        font-weight: 700;
    }
    .mail-message-meta {
        display: grid;
        gap: 0.45rem;
        margin-bottom: 1rem;
        color: #6b7280;
        font-size: 0.95rem;
    }
    .mail-message-body {
        color: #1f2933;
        line-height: 1.75;
        white-space: normal;
    }
    .mail-compose {
        background: #ffffff;
        color: #1f2933;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 50px rgba(59, 41, 25, 0.18);
        border: 1px solid #e7d8bf;
    }
    .mail-compose-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        background: #f8f2e8;
        border-bottom: 1px solid #e7d8bf;
    }
    .mail-compose-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #7f1d1d;
    }
    .mail-compose-window-actions {
        display: inline-flex;
        gap: 0.35rem;
        color: #6b7280;
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
        background: #efe3cd;
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
        border-bottom: 1px solid #e7d8bf;
    }
    .mail-line label {
        min-width: 58px;
        font-size: 0.95rem;
        color: #6b7280;
        padding-top: 0.35rem;
    }
    .mail-line select,
    .mail-line input,
    .mail-compose textarea {
        width: 100%;
        border: 0;
        outline: none;
        background: transparent;
        color: #1f2933;
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
        border-top: 1px solid #e7d8bf;
        background: #f8f2e8;
    }
    .mail-compose-toolbar span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        border-radius: 18px;
        color: #6b7280;
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
        background: #a63d40;
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
        color: #6b7280;
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
        background: #f8f2e8;
    }
    .mail-attachment-field {
        padding: 0 1.25rem 1rem;
    }
    .mail-attachment-field input {
        border: 1px solid #e7d8bf;
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
            <span class="mail-brand-name">Mail</span>
        </a>
        <form class="mail-search" method="GET" action="/mail">
            <span>Suche</span>
            <input type="search" name="search" value="<?= htmlspecialchars((string) ($filters['term'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="In E-Mails suchen">
            <?php
            $activeScopes = (array) ($filters['scope'] ?? ['all']);

            if (in_array('all', $activeScopes, true)) {
                $activeScopes = ['sender', 'recipient', 'content'];
            }
            ?>
            <details class="mail-search-filter">
                <summary>Filter</summary>
                <div class="mail-search-menu" aria-label="Suchfilter">
                    <label><input type="checkbox" name="scope[]" value="sender" <?= in_array('sender', $activeScopes, true) ? 'checked' : '' ?>> Sender</label>
                    <label><input type="checkbox" name="scope[]" value="recipient" <?= in_array('recipient', $activeScopes, true) ? 'checked' : '' ?>> Empfaenger</label>
                    <label><input type="checkbox" name="scope[]" value="content" <?= in_array('content', $activeScopes, true) || in_array('all', $activeScopes, true) ? 'checked' : '' ?>> Inhalt</label>
                </div>
            </details>
            <button type="submit">Filter</button>
        </form>
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
                <li class="mail-folder-item active"><button type="button" class="mail-folder-action" data-mail-view="inbox">Posteingang <span class="mail-folder-count"><?= count($inbox) ?></span></button></li>
                <li class="mail-folder-item"><button type="button" class="mail-folder-action" data-mail-view="marked">Markiert <span class="mail-folder-count"><?= count($markedMessages) ?></span></button></li>
                <li class="mail-folder-item"><button type="button">Zurueckgestellt</button></li>
                <li class="mail-folder-item"><button type="button" class="mail-folder-action" data-mail-view="sent">Gesendet <span class="mail-folder-count"><?= count($sent) ?></span></button></li>
                <li class="mail-folder-item"><button type="button">Entwuerfe</button></li>
                <li class="mail-folder-item"><button type="button">Mehr</button></li>
            </ul>

            <div class="mail-sidebar-heading">
                <span>Labels</span>
                <button class="mail-folder-button" type="button">+</button>
            </div>
            <ul class="mail-mini-list">
                <li class="mail-folder-item"><button type="button" class="mail-folder-action" data-mail-view="team">Abteilungen <span class="mail-folder-count"><?= count($directory) ?></span></button></li>
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
                <button class="mail-tab-button" id="marked-tab" data-bs-toggle="tab" data-bs-target="#mail-marked" type="button" role="tab" aria-controls="mail-marked" aria-selected="false">
                    <span>Markiert</span>
                    <span class="badge rounded-pill"><?= count($markedMessages) ?></span>
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
                                <?php $detail = $buildDetailPayload($message, 'Posteingang'); ?>
                                <article
                                    class="mail-row mail-open-trigger"
                                    role="button"
                                    tabindex="0"
                                    data-detail-folder="<?= htmlspecialchars($detail['folder'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-subject="<?= htmlspecialchars($detail['subject'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-body="<?= htmlspecialchars($detail['body'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-from="<?= htmlspecialchars($detail['from'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-to="<?= htmlspecialchars($detail['to'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-time="<?= htmlspecialchars($detail['created_at'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-attachments="<?= htmlspecialchars($detail['attachments'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-attachments-html="<?= htmlspecialchars($detail['attachments_html'], ENT_QUOTES, 'UTF-8') ?>"
                                >
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
                                            <div class="mail-attachment">
                                                <?php foreach ($message['attachments'] as $attachment): ?>
                                                    <a class="mail-download-link" href="<?= htmlspecialchars((string) ($attachment['download_url'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= htmlspecialchars((string) ($attachment['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mail-row-time"><?= htmlspecialchars((string) ($message['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="tab-pane fade" id="mail-marked" role="tabpanel" aria-labelledby="marked-tab">
                    <div class="mail-list">
                        <div class="mail-list-header">
                            <div>Quelle</div>
                            <div>Betreff</div>
                            <div>Zeit</div>
                        </div>
                        <?php if ($markedMessages === []): ?>
                            <div class="mail-empty">Noch keine markierten Nachrichten.</div>
                        <?php else: ?>
                            <?php foreach ($markedMessages as $message): ?>
                                <?php
                                $sourceLabel = ($message['from'] ?? '') === (string) $user['email'] ? 'Gesendet' : 'Posteingang';
                                $detail = $buildDetailPayload($message, 'Markiert');
                                ?>
                                <article
                                    class="mail-row mail-open-trigger"
                                    role="button"
                                    tabindex="0"
                                    data-detail-folder="<?= htmlspecialchars($detail['folder'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-subject="<?= htmlspecialchars($detail['subject'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-body="<?= htmlspecialchars($detail['body'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-from="<?= htmlspecialchars($detail['from'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-to="<?= htmlspecialchars($detail['to'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-time="<?= htmlspecialchars($detail['created_at'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-attachments="<?= htmlspecialchars($detail['attachments'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-attachments-html="<?= htmlspecialchars($detail['attachments_html'], ENT_QUOTES, 'UTF-8') ?>"
                                >
                                    <div class="mail-row-meta">
                                        <span class="mail-check" aria-hidden="true"></span>
                                        <span class="mail-star" aria-hidden="true">*</span>
                                        <span class="mail-from"><?= htmlspecialchars($sourceLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <div>
                                        <div class="mail-row-content">
                                            <span class="mail-row-subject"><?= htmlspecialchars((string) $message['subject'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <span class="mail-row-snippet">- <?= htmlspecialchars($renderSnippet((string) $message['body']), ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                        <?php if (!empty($message['attachments'])): ?>
                                            <div class="mail-attachment">
                                                <?php foreach ($message['attachments'] as $attachment): ?>
                                                    <a class="mail-download-link" href="<?= htmlspecialchars((string) ($attachment['download_url'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= htmlspecialchars((string) ($attachment['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
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
                                <?php $detail = $buildDetailPayload($message, 'Gesendet'); ?>
                                <article
                                    class="mail-row mail-open-trigger"
                                    role="button"
                                    tabindex="0"
                                    data-detail-folder="<?= htmlspecialchars($detail['folder'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-subject="<?= htmlspecialchars($detail['subject'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-body="<?= htmlspecialchars($detail['body'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-from="<?= htmlspecialchars($detail['from'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-to="<?= htmlspecialchars($detail['to'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-time="<?= htmlspecialchars($detail['created_at'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-attachments="<?= htmlspecialchars($detail['attachments'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-detail-attachments-html="<?= htmlspecialchars($detail['attachments_html'], ENT_QUOTES, 'UTF-8') ?>"
                                >
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
                                            <div class="mail-attachment">
                                                <?php foreach ($message['attachments'] as $attachment): ?>
                                                    <a class="mail-download-link" href="<?= htmlspecialchars((string) ($attachment['download_url'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= htmlspecialchars((string) ($attachment['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
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

            <section class="mail-detail" id="mail-detail-panel">
                <div class="mail-detail-header">
                    <div>
                        <div class="mail-detail-folder" id="mail-detail-folder"><?= htmlspecialchars((string) ($initialDetail['folder'] ?? 'Mail'), ENT_QUOTES, 'UTF-8') ?></div>
                        <h2 class="mail-detail-subject" id="mail-detail-subject"><?= htmlspecialchars((string) ($initialDetail['subject'] ?? 'Bitte links eine Nachricht auswaehlen.'), ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <div class="mail-detail-time" id="mail-detail-time"><?= htmlspecialchars((string) ($initialDetail['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div class="mail-detail-meta">
                    <div><strong>Von:</strong> <span id="mail-detail-from"><?= htmlspecialchars((string) ($initialDetail['from'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                    <div><strong>An:</strong> <span id="mail-detail-to"><?= htmlspecialchars((string) ($initialDetail['to'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                    <div><strong>Anhang:</strong> <span id="mail-detail-attachments"><?= $initialDetail['attachments_html'] ?? '-' ?></span></div>
                </div>
                <div class="mail-detail-body" id="mail-detail-body"><?= nl2br(htmlspecialchars((string) ($initialDetail['body'] ?? 'Noch keine Mail ausgewaehlt.'), ENT_QUOTES, 'UTF-8')) ?></div>
            </section>
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
                        <input id="attachment" name="attachment[]" type="file" multiple accept=".pdf,.doc,.docx,.txt,.xlsx,.csv,.png,.jpg,.jpeg">
                        <div class="mail-attachment-help">Mehrere Dokumente koennen gleichzeitig an interne Nachrichten angehaengt werden.</div>
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

<div class="modal fade mail-message-modal" id="mailMessageModal" tabindex="-1" aria-labelledby="mailMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="mail-detail-folder" id="mail-modal-folder">Mail</div>
                    <h2 class="modal-title fs-4" id="mailMessageModalLabel">Nachricht</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schliessen"></button>
            </div>
            <div class="modal-body">
                <div class="mail-message-meta">
                    <div><strong>Von:</strong> <span id="mail-modal-from">-</span></div>
                    <div><strong>An:</strong> <span id="mail-modal-to">-</span></div>
                    <div><strong>Zeit:</strong> <span id="mail-modal-time">-</span></div>
                    <div><strong>Anhang:</strong> <span id="mail-modal-attachments"><?= $initialDetail['attachments_html'] ?? '-' ?></span></div>
                </div>
                <div class="mail-message-body" id="mail-modal-body">Noch keine Mail ausgewaehlt.</div>
            </div>
            <div class="modal-footer justify-content-start">
                <button type="button" class="btn btn-outline-accent" data-bs-dismiss="modal">Schliessen</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabMap = {
            inbox: '#inbox-tab',
            marked: '#marked-tab',
            sent: '#sent-tab',
            team: '#team-tab',
        };

        const folderItems = Array.from(document.querySelectorAll('.mail-folder-item'));
        const folderButtons = Array.from(document.querySelectorAll('.mail-folder-action'));
        const detailPanel = {
            folder: document.getElementById('mail-detail-folder'),
            subject: document.getElementById('mail-detail-subject'),
            time: document.getElementById('mail-detail-time'),
            from: document.getElementById('mail-detail-from'),
            to: document.getElementById('mail-detail-to'),
            attachments: document.getElementById('mail-detail-attachments'),
            body: document.getElementById('mail-detail-body'),
        };
        const modalElements = {
            folder: document.getElementById('mail-modal-folder'),
            subject: document.getElementById('mailMessageModalLabel'),
            time: document.getElementById('mail-modal-time'),
            from: document.getElementById('mail-modal-from'),
            to: document.getElementById('mail-modal-to'),
            attachments: document.getElementById('mail-modal-attachments'),
            body: document.getElementById('mail-modal-body'),
        };
        const modalNode = document.getElementById('mailMessageModal');
        const mailMessageModal = modalNode && window.bootstrap && window.bootstrap.Modal
            ? window.bootstrap.Modal.getOrCreateInstance(modalNode)
            : null;

        const setActiveFolder = function (view) {
            folderItems.forEach(function (item) {
                const trigger = item.querySelector('.mail-folder-action');
                item.classList.toggle('active', trigger && trigger.dataset.mailView === view);
            });
        };

        folderButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const selector = tabMap[button.dataset.mailView];
                const tabButton = selector ? document.querySelector(selector) : null;

                if (tabButton && window.bootstrap && window.bootstrap.Tab) {
                    window.bootstrap.Tab.getOrCreateInstance(tabButton).show();
                    setActiveFolder(button.dataset.mailView);
                }
            });
        });

        ['#inbox-tab', '#marked-tab', '#sent-tab', '#team-tab'].forEach(function (selector) {
            const tabButton = document.querySelector(selector);

            if (!tabButton) {
                return;
            }

            tabButton.addEventListener('shown.bs.tab', function (event) {
                const mapEntry = Object.entries(tabMap).find(function (entry) {
                    return entry[1] === '#' + event.target.id;
                });

                if (mapEntry) {
                    setActiveFolder(mapEntry[0]);
                }
            });
        });

        document.querySelectorAll('.mail-open-trigger').forEach(function (row) {
            const openRow = function () {
                const detail = {
                    folder: row.dataset.detailFolder || 'Mail',
                    subject: row.dataset.detailSubject || '',
                    time: row.dataset.detailTime || '',
                    from: row.dataset.detailFrom || '-',
                    to: row.dataset.detailTo || '-',
                    attachments: row.dataset.detailAttachments || '-',
                    attachmentsHtml: row.dataset.detailAttachmentsHtml || '-',
                    body: row.dataset.detailBody || '',
                };

                detailPanel.folder.textContent = detail.folder;
                detailPanel.subject.textContent = detail.subject;
                detailPanel.time.textContent = detail.time;
                detailPanel.from.textContent = detail.from;
                detailPanel.to.textContent = detail.to;
                detailPanel.attachments.innerHTML = detail.attachmentsHtml;
                detailPanel.body.innerHTML = detail.body.replace(/\n/g, '<br>');

                modalElements.folder.textContent = detail.folder;
                modalElements.subject.textContent = detail.subject;
                modalElements.time.textContent = detail.time;
                modalElements.from.textContent = detail.from;
                modalElements.to.textContent = detail.to;
                modalElements.attachments.innerHTML = detail.attachmentsHtml;
                modalElements.body.innerHTML = detail.body.replace(/\n/g, '<br>');

                document.querySelectorAll('.mail-open-trigger').forEach(function (entry) {
                    entry.classList.remove('is-selected');
                });

                row.classList.add('is-selected');

                if (mailMessageModal) {
                    mailMessageModal.show();
                }
            };

            row.addEventListener('click', openRow);
            row.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openRow();
                }
            });
        });

        document.querySelectorAll('.mail-download-link').forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        });
    });
</script>
