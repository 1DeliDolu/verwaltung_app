# Login Rate Limiting

## Ziel
- Wiederholte fehlgeschlagene Login-Versuche serverseitig begrenzen.
- Brute-Force-Risiko senken, ohne gueltige Konten oder Passwortdetails preiszugeben.

## Umsetzung
- Neue Migration:
  - `database/migrations/023_create_login_rate_limits_table.sql`
- Neuer Service:
  - `app/Services/LoginThrottleService.php`
  - kombiniert normalisierte E-Mail und IP zu einem stabilen Throttle-Key
  - begrenzt Login-Fehlversuche standardmaessig auf 5 Versuche innerhalb von 15 Minuten
  - sperrt weitere Login-Versuche fuer dieselbe Kombination bis zum Ende des Fensters
  - loescht den Throttle-Eintrag nach erfolgreichem Login
- Auth-Flow:
  - `app/Controllers/AuthController.php`
  - prueft vor dem Passwort-Check auf aktive Sperren
  - setzt nach Fehlschlaegen entweder weiter die generische Fehlermeldung oder die Lockout-Meldung
- Konfiguration:
  - `config/auth.php`
  - `.env.example`
  - `AUTH_LOGIN_MAX_ATTEMPTS`
  - `AUTH_LOGIN_DECAY_SECONDS`

## Hinweise
- Die Sperre bleibt bewusst generisch und verraet nicht, ob E-Mail oder Passwort falsch war.
- Die Slice fuehrt noch kein Forgot-Password oder MFA ein; sie haertet nur den bestehenden Login-Pfad.
