<?php

$authUser = $app->session()->get((string) $app->config('auth.session_key', 'auth_user'));
$isAuthenticated = is_array($authUser);
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$navItems = [
    ['label' => 'News', 'href' => '/news'],
    ['label' => 'Calendar', 'href' => '/calendar'],
];

$navItems[] = $isAuthenticated
    ? ['label' => 'Dashboard', 'href' => '/dashboard']
    : ['label' => 'Login', 'href' => '/login'];
?>
<header class="site-header">
    <a class="brand" href="<?= $isAuthenticated ? '/dashboard' : '/news' ?>">
        <span class="brand-kicker">Workspace</span>
        <span class="brand-name"><?= htmlspecialchars((string) $app->config('app.name', 'Verwaltung App'), ENT_QUOTES, 'UTF-8') ?></span>
        <?php if ((bool) $app->config('app.demo_mode', false)): ?>
            <span class="demo-badge">Demo Umgebung</span>
        <?php endif; ?>
    </a>

    <nav class="site-nav" aria-label="Primary Navigation">
        <?php foreach ($navItems as $item): ?>
            <?php $active = $currentPath === $item['href']; ?>
            <a class="nav-link<?= $active ? ' is-active' : '' ?>" href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </nav>
</header>
