# Central Audit Dashboard Drilldowns

## Ziel
- Dashboard-Zahlen und Zusammenfassungen direkt klickbar machen.
- Drill-downs sollen nur die bestehenden `/audit` Filter nutzen.

## Umsetzung
- Summary-Karten nutzen jetzt generierte Drill-down-URLs.
- Trend-Tage filtern direkt auf den jeweiligen Tag.
- Aktion- und Heatmap-Module setzen Quelle und bei Bedarf `outcome=failure`.
- Actor-Karten setzen `search=<actor_email>`.
- Einzelne Audit-Events setzen Quelle plus einen passenden Suchbegriff fuer den Subject-Kontext.
