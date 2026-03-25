# Verification: Central Audit Dashboard

## Checks
- `php -l app/Controllers/AuditController.php`
- `php -l resources/views/audit/index.php`
- `php tests/run.php`

## Erwartung
- `/audit` ist nur fuer Admin erreichbar.
- Das Dashboard zeigt Summary-Kacheln und einen kombinierten Audit-Stream.
- CSV-Export liefert die zusammengefuehrte Sicht aller Audit-Quellen.
- Die bisherigen spezialisierten Audit-Seiten bleiben unveraendert erreichbar.
