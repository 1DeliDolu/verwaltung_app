<?php $title = 'Infrastruktur'; ?>
<div class="hero">
    <p class="eyebrow">Infrastruktur</p>
    <h1 class="display-6 fw-semibold">Mail- und Dateiserver</h1>
    <p class="lead">Interne Basisdienste fuer Kommunikation und Dokumentenablage mit verantwortlicher Abteilung.</p>
</div>

<div class="row g-4">
    <?php foreach ($services as $service): ?>
        <div class="col-12 col-md-6">
            <article class="card card-soft h-100">
                <p class="eyebrow"><?= htmlspecialchars((string) strtoupper((string) $service['service_type']), ENT_QUOTES, 'UTF-8') ?></p>
                <h2 class="h4"><?= htmlspecialchars((string) $service['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p><strong>Host:</strong> <?= htmlspecialchars((string) $service['host_name'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars((string) $service['status'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Zugriff:</strong> <?= htmlspecialchars((string) $service['access_level'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Verantwortlich:</strong> <?= htmlspecialchars((string) ($service['department_name'] ?? 'Nicht zugeordnet'), ENT_QUOTES, 'UTF-8') ?></p>
                <p><?= htmlspecialchars((string) ($service['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                <?php if ((string) $service['service_type'] === 'file'): ?>
                    <a class="btn btn-outline-accent px-4 py-2 mt-3" href="/services/fileserver">Web-Dateibrowser oeffnen</a>
                <?php endif; ?>
            </article>
        </div>
    <?php endforeach; ?>
</div>
