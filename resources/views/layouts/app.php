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
        .card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 18px 40px rgba(59, 41, 25, 0.08);
            padding: 2rem;
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
        }
        .muted { color: var(--muted); }
    </style>
</head>
<body>
    <div class="shell">
        <?= $content ?>
    </div>
</body>
</html>
