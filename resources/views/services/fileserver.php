<?php $title = 'Dateiserver Browser'; ?>
<div class="hero">
    <p class="eyebrow">Dateiserver</p>
    <h1 class="display-6 fw-semibold">Web-Dateibrowser</h1>
    <p class="lead">Alle fuer dich freigegebenen Abteilungsdateien an einem Ort. Dateien koennen direkt im Browser geoeffnet werden, ohne Samba manuell anzubinden.</p>
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
