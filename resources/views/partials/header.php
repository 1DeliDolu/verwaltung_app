<?php

$authUser = $app->session()->get((string) $app->config('auth.session_key', 'auth_user'));
$isAuthenticated = is_array($authUser);
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$navItems = [
    ['label' => 'News', 'href' => '/news'],
    ['label' => 'Calendar', 'href' => '/calendar'],
];

if ($isAuthenticated) {
    $navItems[] = ['label' => 'Mail', 'href' => '/mail'];
    $navItems[] = ['label' => 'Dashboard', 'href' => '/dashboard'];
} else {
    $navItems[] = ['label' => 'Login', 'href' => '/login'];
}
?>
<header class="site-header navbar navbar-expand-lg px-3 py-3">
    <div class="container-fluid px-0">
        <a class="brand navbar-brand me-0" href="<?= $isAuthenticated ? '/dashboard' : '/news' ?>">
            <span class="brand-kicker">Workspace</span>
            <span class="brand-name"><?= htmlspecialchars((string) $app->config('app.name', 'Verwaltung App'), ENT_QUOTES, 'UTF-8') ?></span>
            <?php if ((bool) $app->config('app.demo_mode', false)): ?>
                <span class="demo-badge">Demo Umgebung</span>
            <?php endif; ?>
        </a>

        <button class="navbar-toggler btn btn-outline-accent ms-3" type="button" data-bs-toggle="collapse" data-bs-target="#primaryNav" aria-controls="primaryNav" aria-expanded="false" aria-label="Navigation umschalten">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-lg-end mt-3 mt-lg-0" id="primaryNav">
            <nav class="navbar-nav gap-2" aria-label="Primary Navigation">
                <?php foreach ($navItems as $item): ?>
                    <?php $active = $currentPath === $item['href']; ?>
                    <a class="nav-link px-3 py-2<?= $active ? ' is-active' : '' ?>" href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
</header>
