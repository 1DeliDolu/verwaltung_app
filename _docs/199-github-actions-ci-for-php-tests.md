# GitHub Actions CI For PHP Tests

## Ziel
- Die bestehende lokale Test-Suite bei jedem Push und Pull Request automatisch ausfuehren.
- Datenbank-Setup fuer CI als repo-lokalen Schritt abbilden statt als undurchsichtige Workflow-Only-Logik.

## Umsetzung
- Neuer Workflow:
  - `.github/workflows/ci.yml`
  - richtet PHP 8.2 und MySQL ein
  - bootstrapped die Testdatenbank
  - startet `php tests/run.php`
- Neuer CLI-Entrypoint:
  - `bin/bootstrap-test-database.php`
  - droppt und erstellt die konfigurierte Testdatenbank neu
  - fuehrt alle Migrationen und Seeds in Repo-Reihenfolge aus
  - verweigert die Ausfuehrung ausserhalb von `APP_ENV=testing` oder `CI=true`
- README:
  - dokumentiert jetzt denselben Bootstrap-Befehl fuer lokale frische Testlaeufe

## Hinweise
- Die Slice fuehrt bewusst kein Framework-Testtool ein; die bestehende leichte Suite bleibt die einzige Test-Entry-Point.
- Hosted GitHub-Ausfuehrung ist mit diesem Slice definiert, aber lokal nicht direkt beobachtbar.
