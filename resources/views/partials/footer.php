<?php

$isDemoMode = (bool) $app->config('app.demo_mode', false);
?>
<footer class="site-footer">
    <div class="row g-4 align-items-start">
        <div class="col-12 col-lg-5">
            <p class="eyebrow">Verwaltung App</p>
            <p class="footer-copy">
                Interne Plattform fuer Dokumente, Infrastruktur und bereichsuebergreifende Zusammenarbeit.
            </p>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <div class="footer-links d-flex flex-wrap gap-3">
                <a href="/news">News</a>
                <a href="/calendar">Calendar</a>
                <a href="/services">Infrastruktur</a>
                <a href="/departments">Abteilungen</a>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="footer-meta d-flex flex-column gap-2 text-md-end text-lg-end">
                <span><?= $isDemoMode ? 'Probe / Demo Umgebung' : 'Interne Umgebung' ?></span>
                <span><?= date('Y') ?> Verwaltung App</span>
            </div>
        </div>
    </div>
</footer>
