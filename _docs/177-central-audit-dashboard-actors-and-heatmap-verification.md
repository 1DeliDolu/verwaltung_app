# Verification: Central Audit Dashboard Actors And Heatmap

## Checks
- `php -l app/Controllers/AuditController.php`
- `php -l resources/views/audit/index.php`
- `php tests/run.php`

## Erwartung
- `/audit` zeigt zusaetzlich:
  - `Aktivste Nutzer`
  - `Failure Heatmap nach Quelle`
- Bestehende Summary- und CSV-Funktionen bleiben intakt.
- Test-Suite bleibt gruen.
