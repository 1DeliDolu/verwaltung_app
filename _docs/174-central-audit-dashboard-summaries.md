# Central Audit Dashboard Summaries

## Ziel
- Dem zentralen Audit-Dashboard schnell erfassbare Trend- und Breakdown-Elemente geben.
- Ohne zusaetzliche JS-Libraries mit vorhandenen Bootstrap-Komponenten arbeiten.

## Umsetzung
- `AuditController` berechnet jetzt:
  - `dailyTrend()` fuer die letzten sieben Tage
  - `actionBreakdown()` fuer Top-Aktionen pro Quelle
- Dashboard zeigt zusaetzlich:
  - Tagestrend mit Erfolg/Fehler-Aufteilung
  - pro Quelle ein Top-Aktionsmodul
- Darstellung erfolgt mit Bootstrap `progress`-Bars und bestehenden Kartenkomponenten.

## Wirkung
- Admin erkennt schneller:
  - ob Fehlercluster an einzelnen Tagen auftreten
  - welche Audit-Quelle gerade am aktivsten ist
  - welche Aktionen eine Quelle dominieren
