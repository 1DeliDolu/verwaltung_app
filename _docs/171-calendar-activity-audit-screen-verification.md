# Verification: Calendar Activity Audit Screen

## Checks
- `php -l app/Controllers/CalendarController.php`
- `php -l app/Services/CalendarService.php`
- `php -l app/Services/AuditLogService.php`
- `php -l resources/views/pages/calendar_audit.php`
- `php tests/run.php`

## Erwartung
- `/calendar/audit` verlangt Login.
- Authentifizierte Nutzer koennen Audit-Seite und CSV-Export aufrufen.
- Kalenderaktionen werden fuer Erstellen, Aktualisieren, Erledigen und Loeschen protokolliert.
- Sichtbarkeit folgt den bestehenden Kalenderrechten.
