# Weekly Audit Report Automation

## Ziel
- Den bestehenden Wochenreport aus `/audit` fuer Cron und andere unattended Operations verfuegbar machen.
- Die bereits fertige Reportlogik wiederverwenden statt eine zweite Delivery-Implementierung aufzubauen.

## Umsetzung
- Neuer CLI-Entrypoint `bin/send-weekly-audit-report.php`
  - bootet die App ueber `bootstrap/console.php`
  - loest einen Admin-Kontext auf
  - unterstuetzt `--dry-run`
  - unterstuetzt `--admin-email`, `--recipient`, `--now` und `--capture-path`
- Neuer `AuditWeeklyReportCommandService`
  - kapselt Admin-Aufloesung
  - kapselt Preview- und Send-Ausfuehrung fuer CLI
  - bleibt auf `AuditWeeklyReportService` als Single Source of Truth aufgesetzt
- Neuer Cron-freundlicher Wrapper:
  - `infra/scripts/send-weekly-audit-report.sh`
- `AuditWeeklyReportService` und `MailService` akzeptieren jetzt gezielte Override-Optionen fuer CLI und Tests.

## Operator Usage
- Dry run:
  - `php bin/send-weekly-audit-report.php --dry-run`
- Explicit send:
  - `php bin/send-weekly-audit-report.php --admin-email=admin@verwaltung.local`
- Capture without SMTP:
  - `php bin/send-weekly-audit-report.php --capture-path=/tmp/audit-weekly-report.jsonl`
- Cron-friendly wrapper:
  - `infra/scripts/send-weekly-audit-report.sh`

## Hinweise
- Ohne `--admin-email` faellt der Command auf `MAIL_AUDIT_REPORT_ADMIN_EMAIL` zurueck.
- Ohne `--recipient` bleibt die bestehende Recipient-Logik des Wochenreports aktiv.
- `--capture-path` ist vor allem fuer Verifikation und kontrollierte Ops-Runs gedacht.
