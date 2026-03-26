# Weekly Audit Host Install Helpers

## Ziel
- Den letzten manuellen Copy-Schritt nach dem Rendern der Weekly-Audit-Host-Assets reduzieren.
- systemd- und cron-Installationen weiter auf denselben Renderer- und Wrapper-Pfad stutzen.

## Umsetzung
- Neue Install-Helper:
  - `infra/scripts/install-weekly-audit-report-systemd.sh`
  - `infra/scripts/install-weekly-audit-report-cron.sh`
- Beide Skripte:
  - rufen zuerst die bestehenden Renderer auf
  - kopieren nur bereits gerenderte Dateien in den gewuenschten Zielpfad
  - setzen die installierten Dateien auf `0644`
  - geben die naechsten Ops-Schritte explizit aus

## Operator Flow
- systemd-Dateien direkt installieren:
  - `sudo infra/scripts/install-weekly-audit-report-systemd.sh /etc/systemd/system www-data www-data admin@verwaltung.local "Mon *-*-* 07:00:00"`
- cron-Datei direkt installieren:
  - `sudo infra/scripts/install-weekly-audit-report-cron.sh /etc/cron.d/verwaltung-weekly-audit-report root admin@verwaltung.local "0 7 * * 1" /var/log/verwaltung-weekly-audit-report.log`
- Danach bleiben host-nahe Aktivierungsschritte bewusst separat:
  - `systemctl daemon-reload`
  - `systemctl enable --now verwaltung-weekly-audit-report.timer`

## Hinweise
- Die Helper fuehren bewusst kein `systemctl` aus, damit sie auch in lokalen oder testnahen Umgebungen ohne laufendes systemd verifizierbar bleiben.
- Die Install-Helper enthalten keine eigene Placeholder-Logik; sie bauen immer auf den vorhandenen Renderern auf.
