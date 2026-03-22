# 34 MailHog App Mailer

## Yapilan Islem

Uygulama icine MailHog SMTP uzerinden test mail gonderebilen minimal mail katmani eklendi.

## Eklenen veya Guncellenen Parcalar

- `.env`
- `.env.example`
- `config/mail.php`
- `bootstrap/app.php`
- `app/Services/MailService.php`
- `app/Controllers/MailController.php`

## Sonuc

- Uygulama artik MailHog SMTP `1025` uzerinden test mail gonderebilir.
- Mail ayarlari config ve env uzerinden okunur.
