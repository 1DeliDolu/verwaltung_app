<?php

$isDemoMode = (bool) $app->config('app.demo_mode', false);
?>
<footer class="site-footer">
    <div>
        <p class="eyebrow">Verwaltung App</p>
        <p class="footer-copy">
            Interne Plattform fuer Dokumente, Infrastruktur und bereichsuebergreifende Zusammenarbeit.
        </p>
    </div>

    <div class="footer-links">
        <a href="/news">News</a>
        <a href="/calendar">Calendar</a>
        <a href="/services">Infrastruktur</a>
        <a href="/departments">Abteilungen</a>
    </div>

    <div class="footer-meta">
        <span><?= $isDemoMode ? 'Probe / Demo Umgebung' : 'Interne Umgebung' ?></span>
        <span><?= date('Y') ?> Verwaltung App</span>
    </div>
</footer>
