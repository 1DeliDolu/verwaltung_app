# 37 Internal Mail Data Layer

## Yapilan Islem

Ic mail icin veri ve servis katmani genisletildi.

## Eklenen veya Guncellenen Parcalar

- `.env`
- `.env.example`
- `config/mail.php`
- `app/Models/User.php`
- `app/Services/MailService.php`
- `app/Services/InternalMailService.php`

## Sonuc

- MailHog API uzerinden gelen/giden liste okunabiliyor.
- Ic kullanici dizini sorgulanabiliyor.
- Uygulama artik kullanici adina genel ic mail gonderebiliyor.
