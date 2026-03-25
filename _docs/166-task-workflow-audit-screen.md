# Task Workflow Audit Screen

## Ziel
- Task-Erstellung, Bearbeitung, Statuswechsel und Kommentare als Audit-Trail sichtbar machen.
- Sichtbarkeit an die bereits vorhandene Department-Access-Logik koppeln.
- Dieselben Filtermuster wie beim Admin-Audit bereitstellen: Suche, Aktion, Outcome, Datum und CSV.

## Umsetzung
- `AuditLogService` um `task_workflow`-Events erweitert.
- Neues Logziel: `storage/logs/task-workflow.log`.
- `TaskController` loggt jetzt:
  - `create_task`
  - `update_task`
  - `update_status`
  - `add_comment`
- Erfolgs- und Fehlerpfade werden jeweils mit `outcome` und optionalem `reason` protokolliert.
- Neue Route: `GET /tasks/audit`.
- Neue View: `resources/views/tasks/audit.php`.
- Nicht-Admin-Nutzer sehen nur Audit-Eintraege aus sichtbaren Abteilungen.
- Tasks-Index verlinkt auf das neue Audit.

## Filter und Export
- `search`
- `department_id`
- `action`
- `outcome`
- `date_from`
- `date_to`
- `format=csv`

## Erfasste Task-Metadaten
- Task-ID
- Titel
- aktueller Status
- Prioritaet
- Department
- Actor
- Statuswechsel `status_from` / `status_to`
- Kommentarvorschau bei `add_comment`
- Faelligkeit und assignee ID bei Create/Update
