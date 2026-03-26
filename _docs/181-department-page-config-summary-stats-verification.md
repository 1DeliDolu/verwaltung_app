# Verification: Department Page Config Summary Stats

## Checks
- `php -l app/Services/DepartmentService.php`
- `php -l app/Controllers/DepartmentController.php`
- `php -l resources/views/departments/index.php`
- `php -l resources/views/departments/show.php`
- `php tests/run.php`

## Erwartung
- `/departments` zeigt auf den Abteilungskarten die konfigurierten Kennzahlen.
- `/departments/hr` zeigt `Mitarbeiter` und `Personalakten`.
- Nicht spezialisierte Abteilungen zeigen keine HR- oder IT-fremden KPI-Labels.
