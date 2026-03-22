# 42 Email Verification Data Layer

## Yapilan Islem

Laravel verification mantigina benzer email verification veri ve servis katmani eklendi.

## Eklenen veya Guncellenen Parcalar

- `database/migrations/009_add_email_verification_to_users.sql`
- `database/seeds/003_admin_user.sql`
- `database/seeds/004_department_users.sql`
- `app/Models/User.php`
- `app/Services/EmailVerificationService.php`

## Sonuc

- Kullanici bazinda verification token ve `email_verified_at` tutuluyor.
- Dogrulama maili MailHog uzerinden gonderilebiliyor.
- Verify link geldiginde kullanici dogrulanabiliyor.
