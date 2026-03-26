# Department Page Config Summary Stats

## Ziel
- Die in `config/departments.php` definierten KPI-Labels nicht nur auf dem Dashboard, sondern auch in den Abteilungsseiten sichtbar machen.
- Die bestehende Service-Logik fuer konfigurationsgetriebene Kennzahlen wiederverwenden statt neue View-Sonderlogik einzubauen.

## Umsetzung
- `DepartmentService` stellt sichtbare Abteilungen jetzt optional direkt mit `summary_stats` bereit.
- `DepartmentController` nutzt diese aufbereiteten Daten fuer `/departments` und `/departments/{slug}`.
- `resources/views/departments/index.php` zeigt die konfigurierten Kennzahlen direkt in den Abteilungskarten.
- `resources/views/departments/show.php` zeigt eine eigene Kennzahlen-Sektion oberhalb der inhaltlichen Bereichskarten.
- Die Labels bleiben weiterhin durch `config/departments.php` und `summaryStatsForDepartment()` gesteuert.

## Hinweise
- IT zeigt dadurch weiter `Verwaltete Konten`.
- HR zeigt weiter `Mitarbeiter` und `Personalakten`.
- Allgemeine Abteilungen bleiben bei den Standardkennzahlen aus dem Profil, ohne HR- oder IT-spezifische Zusatzwerte zu erben.
