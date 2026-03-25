# Mail Activity Audit Screen

## Ziel
- Interne Mail-Aktionen nachvollziehbar machen.
- Datenschutz wahren: normale Nutzer sehen nur Ereignisse, an denen sie beteiligt sind.
- Filter- und CSV-Muster konsistent mit den bestehenden Audit-Seiten halten.

## Umfang
- Neues Audit-Event `mail_activity`
- Neues Logziel `storage/logs/mail-activity.log`
- Erfasste Aktionen:
  - `send_mail`
  - `read_mail`
  - `archive_mail`
  - `restore_mail`
  - `download_attachment`

## Umsetzung
- `InternalMailService::sendInternalMail()` gibt jetzt die erzeugte Mail-ID zurueck.
- `InternalMail::messageForUser()` liefert eine sichtbare Mail inklusive Empfaengerliste und Attachments fuer Audit-Kontext.
- `InternalMailController` protokolliert Erfolgs- und Fehlerpfade.
- Neue Route `GET /mail/audit`.
- Neue View `resources/views/mail/audit.php`.
- Mail-Workspace verlinkt den Audit-Bereich ueber einen `Audit`-Button.

## Sichtbarkeit
- Admin sieht alle Mail-Audit-Eintraege.
- Normale Nutzer sehen nur Eintraege, wenn:
  - sie Actor sind oder
  - sie Sender der Mail sind oder
  - ihre E-Mail in den Empfaengern der Mail auftaucht.

## Filter
- `search`
- `action`
- `outcome`
- `date_from`
- `date_to`
- `format=csv`
