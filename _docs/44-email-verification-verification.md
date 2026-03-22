# 44 Email Verification Verification

## Yapilan Islem

Email verification akisı MailHog uzerinden uçtan uca dogrulandi.

## Uygulanan Adimlar

1. `009_add_email_verification_to_users.sql` migration dosyasi uygulandi.
2. Seed dosyalari ile kullanicilar `unverified` duruma resetlendi.
3. `admin@verwaltung.local` ile login olundu.
4. Uygulama kullaniciyi `/email/verify` sayfasina yonlendirdi.
5. MailHog API icinde `Bitte bestaetige deine E-Mail-Adresse` konulu mail bulundu.
6. Mail govdesindeki verify linki acildi.
7. Kullanici `/dashboard` sayfasina yonlendirildi.
8. Veritabaninda `email_verified_at` alaninin doldugu dogrulandi.

## Sonuc

- Login sonrasi verification zorunlulugu calisiyor.
- Verify maili MailHog uzerinden geliyor.
- Link tiklandiginda kullanici dogrulanip korumali ekranlara erisebiliyor.
