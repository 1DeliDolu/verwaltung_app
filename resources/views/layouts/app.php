<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Verwaltung App', ENT_QUOTES, 'UTF-8') ?></title>
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
        }
        a { color: inherit; }
        .shell {
            width: min(960px, calc(100% - 2rem));
            margin: 0 auto;
            padding: 2rem 0 4rem;
        }
        .site-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1rem 1.25rem;
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
        .site-nav {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.65rem;
        }
        .nav-link {
            text-decoration: none;
            padding: 0.7rem 1rem;
            border-radius: 999px;
            border: 1px solid transparent;
            transition: 160ms ease;
        }
        .nav-link:hover,
        .nav-link.is-active {
            border-color: var(--border);
            background: #fff;
        }
        .card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 18px 40px rgba(59, 41, 25, 0.08);
            padding: 2rem;
        }
        .hero {
            margin-bottom: 1.5rem;
            padding: 2rem 0;
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
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }
        .flash {
            border-radius: 12px;
            padding: 0.9rem 1rem;
            margin-bottom: 1rem;
        }
        .flash-error { background: #fff1f2; color: var(--error); }
        .flash-success { background: #ecfdf5; color: var(--success); }
        .btn {
            background: var(--accent);
            color: #fff;
            border: 0;
            border-radius: 999px;
            padding: 0.8rem 1.2rem;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn:hover { background: var(--accent-dark); }
        .field { margin-bottom: 1rem; }
        .field label { display: block; margin-bottom: 0.4rem; font-weight: 600; }
        .field input {
            width: 100%;
            padding: 0.8rem 0.9rem;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: #fff;
            font-size: 1rem;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            gap: 1rem;
        }
        .muted { color: var(--muted); }
        @media (max-width: 720px) {
            .site-header,
            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }
            .site-nav {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        <?php require dirname(__DIR__) . '/partials/header.php'; ?>
        <?= $content ?>
    </div>
</body>
</html>
