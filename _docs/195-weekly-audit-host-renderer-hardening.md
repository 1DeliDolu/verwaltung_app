# Weekly Audit Host Renderer Hardening

## Ziel
- Weekly-Audit-Host-Renderer ohne `rg`-Abhaengigkeit lauffaehig halten.
- Template-Rendering gegen `#`- und `&`-Zeichen in konfigurierbaren Werten absichern.

## Umsetzung
- Neuer gemeinsamer Helper:
  - `infra/scripts/lib/template-helpers.sh`
- Die Renderer:
  - `infra/scripts/render-weekly-audit-report-systemd.sh`
  - `infra/scripts/render-weekly-audit-report-cron.sh`
  - verwenden jetzt einen gemeinsamen Escape-Helper fuer `sed`-Replacements
  - pruefen unresolved Placeholders mit `grep` statt `rg`

## Verifikation-nahe Anpassungen
- `tests/Feature/AuditWeeklyReportHostAutomationAssetsTest.php`
  - deckt jetzt Sonderzeichen in Admin-Mail und Log-Pfad ab
  - schuetzt damit die neue Escape-Logik fuer beide Renderer

## Hinweise
- Die Install-Helper profitieren indirekt, weil sie weiterhin nur die Renderer delegieren.
- Diese Slice aendert keine Operator-CLI und keine Aktivierungsschritte auf dem Host.
