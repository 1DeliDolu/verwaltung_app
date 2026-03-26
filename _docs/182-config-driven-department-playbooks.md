# Config-Driven Department Playbooks

## Ziel
- Wiederholte Department-Detailkarten aus `resources/views/departments/*/index.php` entfernen, wenn sie nur statische Ein-Karten-Hinweise enthalten.
- Solche Inhalte in `config/departments.php` ablegen, damit Pflege und Erweiterung im Konfigurationslayer bleiben.

## Umsetzung
- Neue optionale Profilstruktur `playbook` in `config/departments.php` fuer einfache Department-Hinweise eingefuehrt.
- `DepartmentService` normalisiert `playbook` und stellt es den Department-Seiten als Teil des angereicherten Profils bereit.
- `resources/views/departments/show.php` rendert jetzt zuerst das konfigurationsgetriebene Playbook und faellt sonst auf ein spezialisiertes Partial zurueck.
- Spezialisierte Partial-Fallbacks werden ueber `detail_partial` im Department-Profil explizit auf das korrekte Verzeichnis gemappt, statt stillschweigend auf den Department-Slug zu vertrauen.
- Ein-Karten-Partialdateien fuer Marketing, Sales, R&D, Finance, Legal, Support, Quality, Production, Supply Chain und General Management wurden entfernt.
- Spezialisierte Mehrspalten-Partialdateien fuer IT, HR und Operations bleiben vorerst bestehen.

## Hinweise
- Der Refactor reduziert wiederholte View-Strukturen ohne Department-Berechtigungen oder Workflow-Aktionen zu aendern.
- Departments mit eigener Sonderdarstellung behalten weiterhin ihren bestehenden Fallback ueber Partialdateien.
