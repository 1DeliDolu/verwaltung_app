# Verification: Central Audit Dashboard Drilldowns

## Checks
- `php -l app/Controllers/AuditController.php`
- `php -l resources/views/audit/index.php`
- `php tests/run.php`

## Erwartung
- Dashboard-Karten und Zusammenfassungen enthalten klickbare Drill-down-Links.
- Die Links setzen passende `/audit` Query-Parameter.
- Test-Suite bleibt gruen.
