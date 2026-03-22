<?php $title = 'Infrastruktur'; ?>
<div class="hero">
    <p class="eyebrow">Infrastruktur</p>
    <h1>Mail- und Dateiserver</h1>
    <p class="lead">Interne Basisdienste fuer Kommunikation und Dokumentenablage mit verantwortlicher Abteilung.</p>
</div>

<div class="grid">
    <?php foreach ($services as $service): ?>
        <article class="card">
            <p class="eyebrow"><?= htmlspecialchars((string) strtoupper((string) $service['service_type']), ENT_QUOTES, 'UTF-8') ?></p>
            <h2><?= htmlspecialchars((string) $service['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p><strong>Host:</strong> <?= htmlspecialchars((string) $service['host_name'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars((string) $service['status'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Zugriff:</strong> <?= htmlspecialchars((string) $service['access_level'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Verantwortlich:</strong> <?= htmlspecialchars((string) ($service['department_name'] ?? 'Nicht zugeordnet'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><?= htmlspecialchars((string) ($service['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        </article>
    <?php endforeach; ?>
</div>
