# Central Audit Dashboard

## Ziel
- Alle bereits vorhandenen Audit-Flaechen in einer zentralen Admin-Ansicht zusammenfassen.
- Schnelles Quellen-Switching zwischen User Management, Tasks, Mail und Calendar ermoeglichen.
- Einheitliche Filter und ein gemeinsamer CSV-Export bereitstellen.

## Umsetzung
- Neuer `AuditController` mit Route `GET /audit`.
- Admin-only Zugriff.
- Merged Stream aus:
  - `readAdminUserEvents()`
  - `readTaskWorkflowEvents()`
  - `readMailActivityEvents()`
  - `readCalendarActivityEvents()`
- Dashboard zeigt:
  - Summary-Kacheln pro Quelle
  - kombinierten Audit-Stream
  - Filter fuer Quelle, Suche, Outcome und Zeitraum
  - CSV-Export
  - Deep Links zu den spezialisierten Detailseiten

## Navigation
- Der Header-Link `Audit` zeigt jetzt auf `/audit`.
- Spezialisierte Seiten bleiben bestehen:
  - `/users/audit`
  - `/tasks/audit`
  - `/mail/audit`
  - `/calendar/audit`
