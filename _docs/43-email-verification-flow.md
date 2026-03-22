# 43 Email Verification Flow

## Yapilan Islem

Verification notice, resend, verify route ve verified-only koruma eklendi.

## Eklenen veya Guncellenen Parcalar

- `app/Middleware/VerifiedMiddleware.php`
- `app/Controllers/VerificationController.php`
- `app/Controllers/AuthController.php`
- `app/Controllers/DashboardController.php`
- `app/Controllers/DepartmentController.php`
- `app/Controllers/InfrastructureController.php`
- `app/Controllers/InternalMailController.php`
- `app/Controllers/MailController.php`
- `resources/views/auth/verify-email.php`
- `resources/views/partials/header.php`
- `routes/web.php`

## Sonuc

- Login sonrasi dogrulanmamis kullanici verification ekranina yonlenir.
- Verifizierungs-E-Mail yeniden gonderilebilir.
- MailHog linkinden gelen verify URL kullaniciyi dogrular.
- Dogrulanmamis kullanici korumali ekranlara giremez.
