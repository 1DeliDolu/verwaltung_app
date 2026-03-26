# Saved Audit Filter Presets

## Ziel
- Wiederkehrende Filterkombinationen im zentralen Audit Dashboard als benannte Presets speicherbar machen.
- Admins sollen Presets direkt aus `/audit` heraus speichern, wieder anwenden und loeschen koennen.

## Umsetzung
- Neue Datenbasis `audit_filter_presets` als eigene SQL-Migration angelegt.
- Neues Model `AuditFilterPreset` fuer Listing, Upsert nach Name und Delete pro Admin-User eingefuehrt.
- Neuer `AuditPresetService` kapselt Filter-Extraktion, Validierung, Preset-URLs und Preset-Zusammenfassungen.
- `AuditController` unterstuetzt jetzt:
  - GET `/audit`
  - POST `/audit/presets`
  - POST `/audit/presets/{id}/delete`
- `resources/views/audit/index.php` zeigt:
  - Alerts fuer Save/Delete-Ergebnis
  - Formular zum Speichern der aktuellen Filter
  - Liste der gespeicherten Presets mit Anwenden- und Loeschen-Aktion

## Hinweise
- Presets bleiben admin-spezifisch.
- Gleiche Preset-Namen aktualisieren den vorhandenen Eintrag statt doppelte Namen anzulegen.
- Preset-Links verwenden weiter die bestehenden `/audit` Query-Parameter, damit kein zweites Filterschema entsteht.
