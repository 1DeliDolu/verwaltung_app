# Verification: Mail Activity Audit Screen

## Checks
- `php -l app/Controllers/InternalMailController.php`
- `php -l app/Services/InternalMailService.php`
- `php -l app/Services/AuditLogService.php`
- `php -l app/Models/InternalMail.php`
- `php -l resources/views/mail/audit.php`
- `php tests/run.php`

## Erwartung
- `/mail/audit` verlangt Login.
- Authentifizierte Nutzer koennen ihre Mail-Audit-Seite und CSV-Export aufrufen.
- Mail-Aktivitaeten werden fuer Senden, Lesen, Archivieren, Wiederherstellen und Download protokolliert.
- Nicht beteiligte Nutzer sehen keine fremden Mail-Audit-Eintraege.
