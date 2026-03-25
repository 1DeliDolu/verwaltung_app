# Central Audit Dashboard Actors And Heatmap

## Ziel
- Audit-Dashboard um zwei weitere Diagnoseebenen erweitern:
  - aktivste Akteure
  - failure-intensivste Quellen

## Umsetzung
- `AuditController` berechnet jetzt:
  - `topActors()`
  - `failureHeatmap()`
- Dashboard zeigt:
  - `Aktivste Nutzer`
  - `Failure Heatmap nach Quelle`

## Nutzen
- Admin sieht schneller:
  - welche Nutzer die meisten Audit-relevanten Aktionen ausloesen
  - welche Quellen aktuell die hoechste Fehlerquote haben
  - ob hoher Durchsatz und hohe Fehlerlast zusammen auftreten
