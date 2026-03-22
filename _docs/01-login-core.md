# 01 Login Core

## Yapilan Islem

Bu asamada uygulamanin minimal HTTP/MVC cekirdegi ve login akisinin uygulama tarafindaki server kodu dolduruldu.

## Eklenen veya Guncellenen Parcalar

- `app/Core/App.php`
- `app/Core/Controller.php`
- `app/Core/Request.php`
- `app/Core/Response.php`
- `app/Core/Router.php`
- `app/Core/Session.php`
- `app/Core/View.php`
- `app/Middleware/AuthMiddleware.php`
- `app/Middleware/CsrfMiddleware.php`
- `app/Controllers/AuthController.php`
- `app/Controllers/DashboardController.php`
- `app/Models/User.php`
- `app/Services/AuthService.php`
- `config/auth.php`
- `bootstrap/app.php`
- `routes/web.php`
- `public/index.php`
- `resources/views/auth/login.php`
- `resources/views/layouts/app.php`
- `resources/views/errors/404.php`
- `resources/views/dashboard/index.php`

## Sonuc

- GET ve POST route tanimlama altyapisi hazirlandi.
- Session ve redirect davranisi calisir hale getirildi.
- View render ve basit layout kullanimi eklendi.
- Login, logout ve korumali dashboard akisinin uygulama kodu yazildi.
- CSRF token uretimi ve dogrulamasi eklendi.

## Not

Bu asama veritabani tablosunu ve varsayilan kullanici kaydini degil, onu kullanacak uygulama akisinin kendisini olusturur.
