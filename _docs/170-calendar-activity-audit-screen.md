# Calendar Activity Audit Screen

## Ziel
- Kalenderaenderungen nachvollziehbar machen.
- Sichtbarkeit an die bestehenden Kalenderrechte koppeln.
- Einheitliche Audit-Oberflaeche mit Suche, Filtern und CSV bereitstellen.

## Umfang
- Neues Audit-Event `calendar_activity`
- Neues Logziel `storage/logs/calendar-activity.log`
- Erfasste Aktionen:
  - `create_event`
  - `update_event`
  - `complete_event`
  - `delete_event`

## Umsetzung
- `CalendarController` schreibt Erfolgs- und Fehlerpfade ins Audit-Log.
- Neue Route `GET /calendar/audit`.
- Neue View `resources/views/pages/calendar_audit.php`.
- Kalenderseite verlinkt das Audit ueber einen `Audit`-Button.
- `CalendarService::auditEventVisibility()` uebernimmt die Sichtbarkeitslogik fuer Audit-Eintraege.

## Sichtbarkeit
- Admin sieht alle Calendar-Audit-Eintraege.
- Normale Nutzer sehen:
  - eigene Termine,
  - department-gebundene Termine sichtbarer Departments,
  - sowie abteilungslose eigene Termine.

## Filter
- `search`
- `department_id`
- `action`
- `outcome`
- `date_from`
- `date_to`
- `format=csv`
