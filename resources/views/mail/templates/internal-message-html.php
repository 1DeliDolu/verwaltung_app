<?php $safeBody = nl2br(htmlspecialchars((string) $body, ENT_QUOTES, 'UTF-8')); ?>
<div style="background:#f4f7fb;padding:24px;font-family:Arial,sans-serif;color:#1f2933;">
    <div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #d9e2ec;border-radius:18px;overflow:hidden;">
        <div style="padding:20px 24px;background:#0d6efd;color:#ffffff;">
            <div style="font-size:12px;letter-spacing:0.08em;text-transform:uppercase;opacity:0.85;">Verwaltung App</div>
            <h1 style="margin:8px 0 0;font-size:24px;line-height:1.2;"><?= htmlspecialchars((string) $subject, ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
        <div style="padding:24px;">
            <p style="margin:0 0 12px;"><strong>Von:</strong> <?= htmlspecialchars((string) $sender_name, ENT_QUOTES, 'UTF-8') ?> &lt;<?= htmlspecialchars((string) $sender_email, ENT_QUOTES, 'UTF-8') ?>&gt;</p>
            <p style="margin:0 0 24px;"><strong>Empfaenger:</strong> <?= htmlspecialchars((string) $recipient_count, ENT_QUOTES, 'UTF-8') ?></p>
            <div style="font-size:15px;line-height:1.7;"><?= $safeBody ?></div>
        </div>
    </div>
</div>
