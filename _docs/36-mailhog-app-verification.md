# 36 MailHog App Verification

## Yapilan Islem

Uygulamadan MailHog'a test mail gonderimi uçtan uca dogrulandi.

## Uygulanan Adimlar

1. PHP mail config ve SMTP servis katmani icin syntax kontrolu yapildi.
2. Uygulama login akisi ile dashboard oturumu acildi.
3. Dashboard uzerinden `Testmail senden` aksiyonu tetiklendi.
4. POST sonrasi `/dashboard` redirect dogrulandi.
5. Dashboard success mesaji goruldu.
6. `http://127.0.0.1:8025/api/v2/messages` uzerinden MailHog API mesaj kaydini dondurdu.

## Sonuc

- Uygulama MailHog SMTP `1025` uzerinden mail gonderiyor.
- Mesaj MailHog kutusuna dustu ve API uzerinden goruldu.
