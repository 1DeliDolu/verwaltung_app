# Verification: Task Workflow Audit Screen

## Checks
- `php -l app/Controllers/TaskController.php`
- `php -l app/Services/AuditLogService.php`
- `php -l resources/views/tasks/audit.php`
- `php tests/run.php`

## Erwartung
- `/tasks/audit` verlangt Login.
- Authentifizierte Nutzer sehen die Audit-Seite.
- Task-Audit-Events koennen nach Aktion, Outcome, Department und Datum gefiltert werden.
- CSV-Export liefert nur die gefilterten, sichtbaren Eintraege.
- Test-Suite bleibt gruen.
