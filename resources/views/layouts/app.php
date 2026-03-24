<?php
$pageClass = trim((string) ($pageClass ?? ''));
$isMailPage = $pageClass === 'page-mail';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Verwaltung App', ENT_QUOTES, 'UTF-8') ?></title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
    >
    <style>
        :root {
            color-scheme: light;
            --bg: #f5efe4;
            --panel: #fffdf8;
            --ink: #1f2933;
            --muted: #6b7280;
            --accent: #a63d40;
            --accent-dark: #7f1d1d;
            --border: #e7d8bf;
            --success: #1f7a4c;
            --error: #9f1239;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            background: radial-gradient(circle at top, #fff8ed 0%, var(--bg) 60%);
            color: var(--ink);
            min-height: 100vh;
        }
        a { color: inherit; }
        .app-shell {
            width: min(1120px, calc(100% - 1.5rem));
            margin: 0 auto;
            padding: 1rem 0 3rem;
        }
        .site-header {
            margin-bottom: 2rem;
            background: rgba(255, 253, 248, 0.88);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 14px 32px rgba(59, 41, 25, 0.08);
            backdrop-filter: blur(10px);
        }
        .brand {
            display: inline-flex;
            flex-direction: column;
            text-decoration: none;
        }
        .brand-kicker {
            color: var(--muted);
            font-size: 0.78rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .brand-name {
            font-size: 1.35rem;
            font-weight: 700;
        }
        .demo-badge {
            display: inline-flex;
            align-items: center;
            margin-top: 0.35rem;
            width: fit-content;
            padding: 0.3rem 0.65rem;
            border-radius: 999px;
            background: #1f7a4c;
            color: #fff;
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .nav-link {
            border-radius: 999px;
            transition: 160ms ease;
            color: var(--ink);
        }
        .nav-link:hover,
        .nav-link.is-active {
            background: #fff;
            color: var(--ink);
        }
        .card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 18px 40px rgba(59, 41, 25, 0.08);
        }
        .hero {
            margin-bottom: 1.5rem;
            padding: 1.5rem 0 0.5rem;
        }
        .eyebrow {
            margin: 0 0 0.4rem;
            color: var(--accent);
            font-size: 0.82rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .lead {
            max-width: 60ch;
            color: var(--muted);
            font-size: 1.05rem;
        }
        .btn {
            background: var(--accent);
            color: #fff;
            border: 0;
            border-radius: 999px;
        }
        .btn:hover { background: var(--accent-dark); }
        .btn-outline-accent {
            border: 1px solid var(--border);
            color: var(--ink);
            border-radius: 999px;
        }
        .btn-outline-accent:hover {
            background: #fff;
            color: var(--ink);
            border-color: var(--border);
        }
        .card.card-soft {
            padding: 1.75rem;
        }
        .form-control,
        .form-select,
        textarea.form-control {
            border-radius: 12px;
            border-color: var(--border);
            padding: 0.8rem 0.9rem;
        }
        .form-control:focus,
        .form-select:focus,
        textarea.form-control:focus {
            border-color: #d5b894;
            box-shadow: 0 0 0 0.25rem rgba(166, 61, 64, 0.12);
        }
        .topbar {
            margin-bottom: 1.5rem;
        }
        .muted { color: var(--muted); }
        .site-footer {
            margin-top: 2rem;
            padding: 1.25rem 1.4rem;
            background: rgba(255, 253, 248, 0.88);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 14px 32px rgba(59, 41, 25, 0.08);
        }
        .footer-copy {
            margin: 0;
            color: var(--muted);
            max-width: 34ch;
            line-height: 1.5;
        }
        .footer-links a {
            text-decoration: none;
            padding-bottom: 0.15rem;
            border-bottom: 1px solid transparent;
        }
        .footer-links a:hover {
            border-color: var(--border);
        }
        .footer-meta {
            color: var(--muted);
        }
        .alert {
            border-radius: 14px;
        }
        .surface-link {
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
        }
        .surface-link:hover .card {
            transform: translateY(-2px);
        }
        .card {
            transition: transform 160ms ease;
        }
        .dashboard-action-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .dashboard-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.85rem;
            margin-top: 1.25rem;
        }
        .dashboard-stat-tile {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            padding: 0.9rem 1rem;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.62);
        }
        .dashboard-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
        }
        .dashboard-stat-label {
            color: var(--muted);
            font-size: 0.84rem;
        }
        .dashboard-role-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
            height: fit-content;
            padding: 0.45rem 0.8rem;
            border: 1px solid var(--border);
            border-radius: 999px;
            color: var(--muted);
            background: rgba(255, 255, 255, 0.6);
            font-size: 0.82rem;
            white-space: nowrap;
        }
        .department-form-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 0;
            background: transparent;
            border: 0;
            color: inherit;
            text-align: left;
        }
        .department-form-toggle-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border: 1px solid var(--border);
            border-radius: 999px;
            color: var(--accent);
            font-size: 1.2rem;
            line-height: 1;
        }
        .department-form-toggle[aria-expanded="true"] .department-form-toggle-icon {
            transform: rotate(45deg);
        }
        .department-form-toggle-icon {
            transition: transform 160ms ease;
        }
        @media (max-width: 720px) {
            .app-shell {
                width: min(100%, calc(100% - 1rem));
            }
            .dashboard-action-grid .btn {
                width: 100%;
            }
        }
        body.page-mail {
            background: radial-gradient(circle at top, #fff8ed 0%, var(--bg) 60%);
            color: var(--ink);
            font-family: "Trebuchet MS", "Segoe UI", sans-serif;
        }
        .page-mail .app-shell {
            width: min(1640px, calc(100% - 1rem));
            padding: 0.5rem 0 1rem;
        }
        .page-mail .alert {
            background: rgba(255, 253, 248, 0.96);
            border-color: var(--border);
            color: var(--ink);
        }
    </style>
</head>
<body class="<?= htmlspecialchars($pageClass, ENT_QUOTES, 'UTF-8') ?>">
    <div class="app-shell">
        <?php require dirname(__DIR__) . '/partials/header.php'; ?>
        <?= $content ?>
        <?php if (!$isMailPage): ?>
            <?php require dirname(__DIR__) . '/partials/footer.php'; ?>
        <?php endif; ?>
    </div>
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"
    ></script>
    <script>
        (function () {
            var hash = window.location.hash;

            if (!hash) {
                return;
            }

            var target = document.querySelector(hash);

            if (!target) {
                return;
            }

            var collapseTarget = null;

            if (target.classList.contains('collapse')) {
                collapseTarget = target;
            } else {
                collapseTarget = target.querySelector('.collapse');
            }

            if (!collapseTarget || typeof bootstrap === 'undefined' || typeof bootstrap.Collapse === 'undefined') {
                return;
            }

            bootstrap.Collapse.getOrCreateInstance(collapseTarget, {
                toggle: false
            }).show();
        }());
    </script>
</body>
</html>
