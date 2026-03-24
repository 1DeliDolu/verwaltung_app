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
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-3">
                    <div>
                        <p class="eyebrow mb-1"><?= htmlspecialchars((string) strtoupper((string) $service['service_type']), ENT_QUOTES, 'UTF-8') ?></p>
                        <h2 class="h4 mb-0"><?= htmlspecialchars((string) $service['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <span class="service-health-badge service-health-<?= htmlspecialchars((string) ($service['health']['state'] ?? 'unknown'), ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars((string) ($service['health']['label'] ?? 'Unknown'), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                <p><strong>Host:</strong> <?= htmlspecialchars((string) $service['host_name'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars((string) $service['status'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Zugriff:</strong> <?= htmlspecialchars((string) $service['access_level'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Verantwortlich:</strong> <?= htmlspecialchars((string) ($service['department_name'] ?? 'Nicht zugeordnet'), ENT_QUOTES, 'UTF-8') ?></p>
                <p><?= htmlspecialchars((string) ($service['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                <?php if (($service['health']['checks'] ?? []) !== []): ?>
                    <div class="service-health-checks">
                        <?php foreach ($service['health']['checks'] as $check): ?>
                            <div class="service-health-check">
                                <span><?= htmlspecialchars((string) $check['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                <strong><?= htmlspecialchars((string) ($check['ok'] ? 'OK' : 'Fehlt'), ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ((string) $service['service_type'] === 'file'): ?>
                    <p class="muted mb-2"><strong>Hybrid:</strong> Derselbe Share kann ueber den Web-Dateibrowser oder ueber Samba/SMB genutzt werden.</p>
                    <a class="btn btn-outline-accent px-4 py-2 mt-3" href="/services/fileserver">Web-Dateibrowser oeffnen</a>
                <?php endif; ?>
            </article>
        </div>
    <?php endforeach; ?>
</div>
