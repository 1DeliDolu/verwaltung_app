<?php $title = 'Dateiserver Browser'; ?>
<div class="hero">
    <p class="eyebrow">Dateiserver</p>
    <h1 class="display-6 fw-semibold">Web-Dateibrowser</h1>
    <p class="lead">Alle fuer dich freigegebenen Abteilungsdateien an einem Ort. Dateien koennen direkt im Browser geoeffnet werden, ohne Samba manuell anzubinden.</p>
</div>

<div class="card card-soft mb-4">
    <p class="eyebrow">Hybrid Zugriff</p>
    <h2 class="h4 mb-3">Ein Share, zwei Wege</h2>
    <p>Diese Ansicht nutzt dieselben Abteilungsordner wie der Samba Fileserver. Du kannst also denselben Datenbestand entweder im Browser oder ueber SMB verwenden.</p>
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="dashboard-stat-tile h-100">
                <span class="dashboard-stat-value">Web</span>
                <span class="dashboard-stat-label">Empfohlen fuer schnelles Oeffnen, Lesen und Pruefen direkt in der Anwendung.</span>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="dashboard-stat-tile h-100">
                <span class="dashboard-stat-value">SMB</span>
                <span class="dashboard-stat-label">Geeignet fuer Netzlaufwerke, Explorer/Finder-Workflows und externe Bearbeitung ausserhalb der Web-App.</span>
            </div>
        </div>
    </div>
    <hr>
    <p class="mb-2"><strong>Demo-Port:</strong> `localhost:1445` stellt den Samba-Dienst bereit, nicht eine HTTP-Webseite.</p>
    <p class="mb-0 muted">Fuer Browserzugriff nutze diese Seite. Fuer klassische Netzlaufwerksnutzung ist ein SMB-Client noetig; auf Standardumgebungen ist dafuer typischerweise Port `445` komfortabler als der Demo-Port `1445`.</p>
</div>

<?php if ($shares === []): ?>
    <div class="card card-soft">
        <p class="mb-0 muted">Keine zugaenglichen Dateifreigaben gefunden.</p>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($shares as $share): ?>
            <?php $department = $share['department']; ?>
            <?php $files = $share['files']; ?>
            <div class="col-12">
                <section class="card card-soft h-100">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                        <div>
                            <p class="eyebrow"><?= htmlspecialchars((string) ($department['membership_role'] ?? $user['role_name'] ?? 'member'), ENT_QUOTES, 'UTF-8') ?></p>
                            <h2 class="h4 mb-1"><?= htmlspecialchars((string) $department['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <p class="muted mb-0"><?= htmlspecialchars((string) ($department['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <a class="btn btn-outline-accent px-4 py-2" href="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>">
                            Bereich oeffnen
                        </a>
                    </div>

                    <?php if ($files === []): ?>
                        <p class="muted mb-0">Noch keine Dateien in dieser Freigabe vorhanden.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Datei</th>
                                        <th>Pfad</th>
                                        <th>Groesse</th>
                                        <th>Geaendert</th>
                                        <th>Aktion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($files as $file): ?>
                                        <tr>
                                            <td><?= htmlspecialchars((string) $file['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string) $file['path'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string) $file['size'], ENT_QUOTES, 'UTF-8') ?> B</td>
                                            <td><?= htmlspecialchars((string) $file['modified_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <a href="/departments/<?= htmlspecialchars((string) $department['slug'], ENT_QUOTES, 'UTF-8') ?>/files/open?path=<?= rawurlencode((string) $file['path']) ?>" target="_blank" rel="noreferrer">
                                                    Oeffnen
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
