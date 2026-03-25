# Verification: Central Audit Dashboard Summaries

## Checks
- `php -l app/Controllers/AuditController.php`
- `php -l resources/views/audit/index.php`
- `php tests/run.php`

## Erwartung
- `/audit` zeigt neben den Summary-Kacheln auch:
  - `Letzte 7 Tage`
  - `Top Aktionen nach Quelle`
- CSV-Export bleibt unveraendert funktionsfaehig.
- Test-Suite bleibt gruen.
