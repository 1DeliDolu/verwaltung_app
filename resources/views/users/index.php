<?php $title = 'Users'; ?>
<div class="hero">
    <p class="eyebrow">Admin</p>
    <h1 class="display-6 fw-semibold">Bereichsleiter Verwaltung</h1>
    <p class="lead">Alle `leiter.*@verwaltung.local` Konten werden hier zentral fuer Demo, Login, Bereichszuordnung und Rollenpflege gelistet.</p>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="card card-soft mb-4">
    <div class="row g-3 align-items-center">
        <div class="col-12 col-lg-8">
            <h2 class="h4 mb-2">Standardzugang fuer Bereichsleiter</h2>
            <p class="muted mb-0">Admins koennen das Demo-Passwort jederzeit neu setzen. Ein Reset erzwingt die Passwortaenderung beim naechsten Login.</p>
        </div>
        <div class="col-12 col-lg-4">
            <div class="dashboard-role-badge justify-content-center w-100">
                Passwort: <?= htmlspecialchars((string) $defaultLeaderPassword, ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    </div>
</div>

<div class="card card-soft mb-4">
    <form method="GET" action="/users" class="row g-3 align-items-end">
        <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold" for="search">Suche</label>
            <input
                class="form-control"
                id="search"
                name="search"
                value="<?= htmlspecialchars((string) ($filters['search'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Name, E-Mail oder Abteilung"
            >
        </div>
        <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold" for="department">Abteilung</label>
            <select class="form-select" id="department" name="department">
                <option value="">Alle Abteilungen</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= htmlspecialchars((string) ($department['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($filters['department'] ?? '') === (string) ($department['slug'] ?? '') ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) ($department['name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold" for="membership_role">Mitgliedschaft</label>
            <select class="form-select" id="membership_role" name="membership_role">
                <option value="">Alle Rollen</option>
                <?php foreach ($membershipRoles as $roleKey => $roleLabel): ?>
                    <option value="<?= htmlspecialchars((string) $roleKey, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($filters['membership_role'] ?? '') === (string) $roleKey ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $roleLabel, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 d-flex flex-wrap gap-2">
            <button class="btn px-4 py-2" type="submit">Filter anwenden</button>
            <a class="btn btn-outline-accent px-4 py-2" href="/users">Zuruecksetzen</a>
        </div>
    </form>
</div>

<div class="card card-soft">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Leiterkonten</p>
            <h2 class="h4 mb-0"><?= count($leaders) ?> Eintraege</h2>
        </div>
        <div class="dashboard-role-badge">
            Nur fuer Admin sichtbar
        </div>
    </div>

    <?php if ($leaders === []): ?>
        <p class="muted mb-0">Keine Bereichsleiter gefunden.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Abteilung</th>
                        <th scope="col">Name</th>
                        <th scope="col">E-Mail</th>
                        <th scope="col">Rolle</th>
                        <th scope="col">Passwortstatus</th>
                        <th scope="col">Zuordnung</th>
                        <th scope="col" class="text-end">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaders as $leader): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($leader['department_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($leader['name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><code><?= htmlspecialchars((string) ($leader['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td><?= htmlspecialchars((string) ($leader['membership_role'] ?? $leader['role_name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if (!empty($leader['password_change_required_at'])): ?>
                                    <span class="badge text-bg-warning">Wechsel erforderlich</span>
                                <?php elseif (!empty($leader['password_changed_at'])): ?>
                                    <span class="badge text-bg-success">Aktualisiert</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Unbekannt</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="/users/<?= htmlspecialchars((string) ($leader['id'] ?? 0), ENT_QUOTES, 'UTF-8') ?>/assignment" class="d-grid gap-2">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <select class="form-select form-select-sm" name="department_id" required>
                                        <?php foreach ($departments as $department): ?>
                                            <option value="<?= htmlspecialchars((string) ($department['id'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($leader['department_slug'] ?? '') === (string) ($department['slug'] ?? '') ? 'selected' : '' ?>>
                                                <?= htmlspecialchars((string) ($department['name'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select class="form-select form-select-sm" name="membership_role" required>
                                        <?php foreach ($membershipRoles as $roleKey => $roleLabel): ?>
                                            <option value="<?= htmlspecialchars((string) $roleKey, ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($leader['membership_role'] ?? '') === (string) $roleKey ? 'selected' : '' ?>>
                                                <?= htmlspecialchars((string) $roleLabel, ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-sm px-3 py-2" type="submit">Zuordnung speichern</button>
                                </form>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="/users/<?= htmlspecialchars((string) ($leader['id'] ?? 0), ENT_QUOTES, 'UTF-8') ?>/reset-password" onsubmit="return window.confirm('Passwort fuer dieses Leiterkonto wirklich auf den Standardwert zuruecksetzen?');">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <button class="btn btn-outline-accent btn-sm px-3 py-2" type="submit">Passwort reset</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
