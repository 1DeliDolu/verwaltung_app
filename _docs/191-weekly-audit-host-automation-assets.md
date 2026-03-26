# Weekly Audit Host Automation Assets

## Ziel
- Host-ops fuer den Wochenreport mit fertigen systemd- und cron-Assets unterstuetzen.
- Hart codierte absolute Pfade in committeten Host-Dateien vermeiden.

## Umsetzung
- Neue Example-Templates:
  - `infra/examples/weekly-audit-report.service.example`
  - `infra/examples/weekly-audit-report.timer.example`
  - `infra/examples/weekly-audit-report.cron.example`
- Neue Renderer:
  - `infra/scripts/render-weekly-audit-report-systemd.sh`
  - `infra/scripts/render-weekly-audit-report-cron.sh`
- Renderer setzen Platzhalter fuer:
  - App-Root
  - Host-User / Group
  - Admin-E-Mail
  - systemd `OnCalendar`
  - Cron Schedule
  - Log-Pfad

## Operator Flow
- systemd-Dateien rendern:
  - `infra/scripts/render-weekly-audit-report-systemd.sh /tmp/systemd`
- cron-Datei rendern:
  - `infra/scripts/render-weekly-audit-report-cron.sh /tmp/verwaltung-weekly-audit-report`
- Danach koennen die gerenderten Dateien manuell in `/etc/systemd/system/` bzw. `/etc/cron.d/` installiert werden.

## Hinweise
- Die Templates rufen weiter nur `infra/scripts/send-weekly-audit-report.sh` auf.
- Die Repo-Seite rendert Assets, fuehrt aber bewusst keine Root-Installation auf dem Host selbst durch.
