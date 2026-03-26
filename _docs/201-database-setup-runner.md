# Database Setup Runner

## Ziel
- Manuelle Ausfuehrung von Migration- und Seed-SQL-Dateien durch einen repo-lokalen Ein-Kommando-Runner ersetzen.
- Lokale und gehostete Umgebungen auf denselben DB-Setup-Pfad bringen.

## Umsetzung
- Neuer Shared Service:
  - `app/Services/DatabaseSetupService.php`
  - erstellt die konfigurierte Datenbank bei Bedarf
  - trackt ausgefuehrte Migrationen in `schema_migrations`
  - trackt ausgefuehrte Seeds in `database_seed_runs`
  - adoptiert bestehende manuell vorbereitete Datenbanken in diese Tracking-Tabellen
  - unterstuetzt `--dry-run` und guarded `--fresh`
- Neuer CLI-Entrypoint:
  - `bin/setup-database.php`
  - Standardlauf = pending Migrationen + pending Seeds
  - `--migrate-only`, `--seed-only`, `--dry-run`, `--fresh`
- Test/CI-Integration:
  - `bin/bootstrap-test-database.php` delegiert jetzt an denselben Shared Service
  - GitHub Actions nutzt jetzt `php bin/setup-database.php --fresh`
- README:
  - dokumentiert den neuen allgemeinen DB-Setup-Flow

## Hinweise
- Bereits angewendete Migration- oder Seed-Dateien werden nicht erneut ausgefuehrt.
- Bei einer bereits manuell vorbereiteten Datenbank markiert der erste Lauf die vorhandene Historie als uebernommen, statt alte SQL-Dateien erneut auszufuehren.
- Fuer spaetere Daten- oder Schema-Aenderungen sollen neue geordnete SQL-Dateien hinzugefuegt werden, statt bestehende Dateien umzuschreiben.
