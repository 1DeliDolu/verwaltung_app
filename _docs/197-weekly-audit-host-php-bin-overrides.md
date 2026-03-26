# Weekly Audit Host PHP Binary Overrides

## Ziel
- Weekly-Audit-Host-Assets fuer Hosts mit mehreren PHP-Binaries berechenbar machen.
- Die bereits vorhandene `PHP_BIN`-Unterstuetzung des Wrappers auch in systemd- und cron-Assets explizit nutzbar machen.

## Umsetzung
- Templates:
  - `infra/examples/weekly-audit-report.service.example`
  - `infra/examples/weekly-audit-report.cron.example`
  - rendern jetzt einen expliziten `PHP_BIN`-Wert
- Renderer und Installer:
  - akzeptieren optional einen letzten `PHP_BIN`-Parameter
  - bleiben rueckwaertskompatibel und fallen weiter auf `php` zurueck
- Ops-Doku:
  - zeigt jetzt Beispielaufrufe mit `/usr/bin/php8.2`
  - nennt den finalen `PHP_BIN`-Parameter als Host-spezifische Option

## Hinweise
- Die Wrapper-Semantik selbst bleibt unveraendert; `infra/scripts/send-weekly-audit-report.sh` bleibt die Single Source of Truth fuer die eigentliche CLI-Ausfuehrung.
- Die neue Option ist fuer pfadartige Werte gedacht, etwa `php` oder `/usr/bin/php8.2`.
