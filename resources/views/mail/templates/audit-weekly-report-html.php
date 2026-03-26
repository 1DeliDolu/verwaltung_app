<div style="background:#f4f7fb;padding:24px;font-family:Arial,sans-serif;color:#1f2933;">
    <div style="max-width:760px;margin:0 auto;background:#ffffff;border:1px solid #d9e2ec;border-radius:18px;overflow:hidden;">
        <div style="padding:20px 24px;background:#0f172a;color:#ffffff;">
            <div style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;opacity:0.8;">Verwaltung App</div>
            <h1 style="margin:8px 0 0;font-size:24px;line-height:1.2;"><?= htmlspecialchars((string) ($subject ?? 'Audit Wochenreport'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p style="margin:8px 0 0;font-size:14px;opacity:0.85;">Zeitraum: <?= htmlspecialchars((string) ($window['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div style="padding:24px;">
            <p style="margin:0 0 10px;"><strong>Erstellt am:</strong> <?= htmlspecialchars((string) ($generated_at ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <p style="margin:0 0 20px;"><strong>Empfaenger:</strong> <?= htmlspecialchars(implode(', ', (array) ($recipients ?? [])), ENT_QUOTES, 'UTF-8') ?></p>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin:0 0 24px;">
                <div style="padding:16px;border:1px solid #d9e2ec;border-radius:14px;background:#f8fafc;">
                    <div style="font-size:12px;text-transform:uppercase;color:#64748b;">Events</div>
                    <div style="font-size:24px;font-weight:700;"><?= htmlspecialchars((string) ($stats['total'] ?? 0), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div style="padding:16px;border:1px solid #d9e2ec;border-radius:14px;background:#f8fafc;">
                    <div style="font-size:12px;text-transform:uppercase;color:#64748b;">Erfolg</div>
                    <div style="font-size:24px;font-weight:700;"><?= htmlspecialchars((string) ($stats['success'] ?? 0), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div style="padding:16px;border:1px solid #d9e2ec;border-radius:14px;background:#f8fafc;">
                    <div style="font-size:12px;text-transform:uppercase;color:#64748b;">Fehler</div>
                    <div style="font-size:24px;font-weight:700;"><?= htmlspecialchars((string) ($stats['failure'] ?? 0), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div style="padding:16px;border:1px solid #d9e2ec;border-radius:14px;background:#f8fafc;">
                    <div style="font-size:12px;text-transform:uppercase;color:#64748b;">Failure Rate</div>
                    <div style="font-size:24px;font-weight:700;"><?= htmlspecialchars((string) ($stats['failure_rate'] ?? 0), ENT_QUOTES, 'UTF-8') ?>%</div>
                </div>
            </div>

            <h2 style="margin:0 0 12px;font-size:18px;">Quellen</h2>
            <table style="width:100%;border-collapse:collapse;margin:0 0 24px;">
                <tbody>
                <?php foreach ((array) ($source_summary ?? []) as $source): ?>
                    <tr>
                        <td style="padding:10px 0;border-bottom:1px solid #e2e8f0;"><?= htmlspecialchars((string) ($source['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="padding:10px 0;border-bottom:1px solid #e2e8f0;text-align:right;font-weight:700;"><?= htmlspecialchars((string) ($source['count'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <h2 style="margin:0 0 12px;font-size:18px;">Aktivste Nutzer</h2>
            <?php if (($top_actors ?? []) === []): ?>
                <p style="margin:0 0 24px;">Keine Nutzerdaten im Berichtfenster.</p>
            <?php else: ?>
                <ul style="margin:0 0 24px;padding-left:18px;">
                    <?php foreach ((array) ($top_actors ?? []) as $actor): ?>
                        <li style="margin:0 0 8px;"><?= htmlspecialchars((string) ($actor['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars((string) ($actor['total'] ?? 0), ENT_QUOTES, 'UTF-8') ?> Events, <?= htmlspecialchars((string) ($actor['failure'] ?? 0), ENT_QUOTES, 'UTF-8') ?> Fehler</li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <h2 style="margin:0 0 12px;font-size:18px;">Letzte Fehler</h2>
            <?php if (($recent_failures ?? []) === []): ?>
                <p style="margin:0 0 24px;">Keine Fehler im Berichtfenster.</p>
            <?php else: ?>
                <div style="display:grid;gap:12px;margin:0 0 24px;">
                    <?php foreach ((array) ($recent_failures ?? []) as $failure): ?>
                        <div style="padding:14px;border:1px solid #fecaca;border-radius:14px;background:#fff7f7;">
                            <div style="font-weight:700;"><?= htmlspecialchars((string) ($failure['source_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars((string) ($failure['action'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            <div style="margin-top:4px;font-size:14px;"><?= htmlspecialchars((string) ($failure['subject'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            <div style="margin-top:4px;font-size:13px;color:#64748b;"><?= htmlspecialchars((string) ($failure['timestamp'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            <div style="margin-top:6px;font-size:13px;color:#7f1d1d;"><?= htmlspecialchars((string) ($failure['reason'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <p style="margin:0;font-size:14px;color:#475569;"><strong>Anhang:</strong> <?= htmlspecialchars((string) ($csv_name ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>
</div>
