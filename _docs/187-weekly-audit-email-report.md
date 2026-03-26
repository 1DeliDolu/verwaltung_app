# Weekly Audit Email Report

## Ziel
- Den zentralen Audit-Ueberblick der letzten 7 Tage als wiederverwendbaren Mail-Report fuer Admins verfuegbar machen.
- Die gleiche Aggregation fuer Dashboard und Report verwenden, damit Kennzahlen und CSV-Anhang nicht auseinanderlaufen.

## Umsetzung
- Neuer `AuditDashboardService` kapselt die zentrale Aggregation fuer:
  - Quell-Summaries
  - Event-Merge
  - Trend
  - Action Breakdown
  - Top Actors
  - Failure Heatmap
  - CSV Export
- Neuer `AuditWeeklyReportService` erstellt einen festen Wochenreport:
  - Zeitraum = heute und die sechs vorherigen Tage
  - immer alle zentralen Audit-Quellen
  - kompaktes Text/HTML-Mailformat
  - CSV-Anhang fuer denselben Zeitraum
- `AuditController` unterstuetzt jetzt zusaetzlich den admin-only POST-Flow fuer den Wochenreport.
- `resources/views/audit/index.php` zeigt eine eigene Wochenreport-Karte mit:
  - Report-Zeitraum
  - Empfaengern
  - Hinweis, dass aktive Dashboard-Filter ignoriert werden
  - Sendeaktion
- `MailService` kann optional Nachrichten in eine lokale Capture-Datei schreiben; das erleichtert deterministiche Feature-Tests ohne SMTP-Abhaengigkeit.

## Konfiguration
- `MAIL_AUDIT_REPORT_RECIPIENTS` kann eine kommaseparierte Empfaengerliste definieren.
- Wenn keine Empfaenger konfiguriert sind, faellt der Report auf die aktuelle Admin-Mailadresse zurueck.
- `MAIL_CAPTURE_PATH` und `MAIL_AUDIT_REPORT_NOW` sind optionale lokale bzw. testnahe Hooks fuer deterministische Verifikation.

## Hinweise
- Die Slice liefert bewusst nur den manuellen Admin-Trigger; eine spaetere Cron-Integration kann denselben Service wiederverwenden.
- Der Report basiert nicht auf den gerade gesetzten `/audit` Filtern, sondern auf einem stabilen Wochenfenster fuer alle Quellen.
