# Verification: Config-Driven Department Playbooks

## Checks
- `php -l config/departments.php`
- `php -l app/Services/DepartmentService.php`
- `php -l resources/views/departments/show.php`
- `php -l tests/Feature/DepartmentPagesTest.php`
- `php tests/run.php`

## Erwartung
- Marketing und andere einfache Departments rendern ihre Hinweis-Karte direkt aus der Konfiguration.
- IT, HR und Operations rendern weiterhin ihre spezialisierten Partialinhalte.
- Entfernte Ein-Karten-Partialdateien fuehren nicht zu leeren Department-Seiten.
